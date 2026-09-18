<?php

declare(strict_types=1);

namespace App\Bugsnag;

use App\Models\Website;
use Illuminate\Support\Collection;

/**
 * Keeps each website's Bugsnag open-error count up to date from the hub,
 * using one agency-wide auth token. Linking a website to its project is a
 * separate, occasional step; syncing the counts runs on the schedule.
 *
 * A failed sync never clears the last known count — an outage at Bugsnag
 * should read as "this number is old", not as "the site is now clean".
 */
final readonly class BugsnagSync
{
    public function __construct(private BugsnagApi $api) {}

    public function isConfigured(): bool
    {
        return $this->api->isConfigured();
    }

    /**
     * Matches every website to a Bugsnag project and stores its id. A
     * website is matched by its stored project API key first, then by an
     * already-known project URL, then by name against its label or domain.
     *
     * @return array{linked: list<string>, unmatched: list<string>}
     *
     * @throws BugsnagApiException
     */
    public function linkProjects(bool $relink = false): array
    {
        $projects = collect($this->api->projects($this->api->organizationId()));

        $linked = [];
        $unmatched = [];

        foreach (Website::query()->orderBy('label')->get() as $website) {
            if ($website->bugsnag_project_id !== null && ! $relink) {
                continue;
            }

            $project = $this->matchProject($website, $projects);

            if ($project === null) {
                $unmatched[] = $website->label;

                continue;
            }

            $website->forceFill([
                'bugsnag_project_id' => $project->id,
                'bugsnag_project_url' => $website->bugsnag_project_url ?? $project->url,
            ])->save();

            $linked[] = "{$website->label} → {$project->name}";
        }

        return ['linked' => $linked, 'unmatched' => $unmatched];
    }

    /**
     * Pulls the open-error count for every linked website.
     *
     * @return array{synced: int, failed: int}
     */
    public function syncAll(): array
    {
        $synced = 0;
        $failed = 0;

        foreach (Website::query()->whereNotNull('bugsnag_project_id')->get() as $website) {
            $this->syncWebsite($website) ? $synced++ : $failed++;
        }

        return ['synced' => $synced, 'failed' => $failed];
    }

    public function syncWebsite(Website $website): bool
    {
        if ($website->bugsnag_project_id === null) {
            return false;
        }

        try {
            $openErrors = $this->api->openErrorCount($website->bugsnag_project_id);
        } catch (BugsnagApiException $exception) {
            $website->forceFill(['bugsnag_sync_error' => $exception->getMessage()])->save();

            return false;
        }

        $website->forceFill([
            'bugsnag_open_errors' => $openErrors,
            'bugsnag_synced_at' => now(),
            'bugsnag_sync_error' => null,
        ])->save();

        return true;
    }

    /**
     * @param  Collection<int, BugsnagProject>  $projects
     */
    private function matchProject(Website $website, Collection $projects): ?BugsnagProject
    {
        $apiKey = $website->bugsnag_project_key;

        if (filled($apiKey)) {
            $byKey = $projects->first(fn (BugsnagProject $project): bool => $project->apiKey === $apiKey);

            if ($byKey instanceof BugsnagProject) {
                return $byKey;
            }
        }

        if (filled($website->bugsnag_project_url)) {
            $url = $this->normalize($website->bugsnag_project_url);

            $byUrl = $projects->first(
                fn (BugsnagProject $project): bool => $project->url !== null && $this->normalize($project->url) === $url,
            );

            if ($byUrl instanceof BugsnagProject) {
                return $byUrl;
            }
        }

        $candidates = array_filter([$website->label, $website->domain()]);

        $byName = $projects->first(function (BugsnagProject $project) use ($candidates): bool {
            foreach ($candidates as $candidate) {
                if (mb_strtolower($project->name) === mb_strtolower((string) $candidate)) {
                    return true;
                }
            }

            return false;
        });

        return $byName instanceof BugsnagProject ? $byName : null;
    }

    private function normalize(string $url): string
    {
        return mb_rtrim(mb_strtolower($url), '/');
    }
}
