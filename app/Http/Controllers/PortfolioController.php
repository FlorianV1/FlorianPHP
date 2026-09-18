<?php

namespace App\Http\Controllers;

use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\NowItem;
use App\Models\PageView;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Settings;
use App\Models\Skill;
use App\Models\Stat;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $this->trackPageView($request, '/');

        $profile = Profile::first();
        $nowItems = NowItem::active()->ordered()->get();

        // Visibility is `is_posted`. This used to filter on `is_ongoing`, which
        // meant a finished project silently vanished from the site.
        $projects = Project::posted()->ordered()->get();

        $experiences = Experience::active()->work()->ordered()->get();
        $education = Experience::active()->education()->ordered()->get();
        $skills = Skill::active()->ordered()->get();
        $services = Service::active()->ordered()->get();
        $testimonials = Testimonial::active()->ordered()->with('project')->get();
        $stats = Stat::active()->ordered()->get();

        // THEME / COLORS
        $colors = Settings::get('custom_colors', [
            'app_bg'         => '#0E0E10',
            'surface'        => '#111418',
            'accent'         => '#4A9FFF',
            'accent_hover'   => '#2D7CE8',
            'text_primary'   => '#E7EAF0',
            'text_secondary' => '#A8ACB3',
            'text_muted'     => '#6F737A',
        ]);

        $overlay           = Settings::get('overlay', 'none');
        $overlayIntensity  = Settings::get('overlay_intensity', 50);

        // SECTIONS ORDER – if nothing saved yet, use a default
        $sectionsOrder = Settings::get('sections_order', null);
        if (! is_array($sectionsOrder) || empty($sectionsOrder)) {
            $sectionsOrder = self::defaultSectionsOrder();
        }

        // Sections added after a site was first configured are absent from the
        // stored order; append them so new content is never invisible.
        $stored = collect($sectionsOrder)->pluck('section')->all();
        foreach (self::defaultSectionsOrder() as $default) {
            if (! in_array($default['section'], $stored, true)) {
                $sectionsOrder[] = $default;
            }
        }

        // NAVBAR LINKS – fallback if no settings yet
        $navbarLinks = Settings::get('navbar_links', [
            ['label' => 'Projects',   'url' => '#projects',   'enabled' => true],
            ['label' => 'Experience', 'url' => '#experience', 'enabled' => true],
            ['label' => 'About',      'url' => '#about',      'enabled' => true],
            ['label' => 'Contact',    'url' => '#contact',    'enabled' => true],
        ]);

        return view('portfolio', [
            'profile'          => $profile,
            'nowItems'         => $nowItems,
            'projects'         => $projects,
            'experiences'      => $experiences,
            'education'        => $education,
            'skills'           => $skills,
            'services'         => $services,
            'testimonials'     => $testimonials,
            'stats'            => $stats,
            'colors'           => $colors,
            'overlay'          => $overlay,
            'overlayIntensity' => $overlayIntensity,
            'sectionsOrder'    => $sectionsOrder,
            'navbarLinks'      => $navbarLinks,
        ]);
    }

    /**
     * Case-study page for a single project. There is no `has_case_study` flag —
     * a case study exists exactly when a posted project has a body.
     */
    public function caseStudy(Request $request, Project $project)
    {
        abort_unless($project->hasCaseStudy(), Response::HTTP_NOT_FOUND);

        $this->trackPageView($request, '/work/' . $project->slug);

        $profile = Profile::first();

        return view('case-study', [
            'profile'      => $profile,
            'project'      => $project,
            'testimonials' => $project->testimonials()->where('is_active', true)->orderBy('sort_order')->get(),
            'related'      => Project::posted()
                ->whereKeyNot($project->getKey())
                ->ordered()
                ->take(3)
                ->get(),
            'colors'       => Settings::get('custom_colors', []),
        ]);
    }

    public static function defaultSectionsOrder(): array
    {
        return [
            ['section' => 'hero',         'enabled' => true],
            ['section' => 'services',     'enabled' => true],
            ['section' => 'now',          'enabled' => true],
            ['section' => 'projects',     'enabled' => true],
            ['section' => 'testimonials', 'enabled' => true],
            ['section' => 'experience',   'enabled' => true],
            ['section' => 'education',    'enabled' => true],
            ['section' => 'skills',       'enabled' => true],
            ['section' => 'about',        'enabled' => true],
            ['section' => 'contact',      'enabled' => true],
        ];
    }

    private function trackPageView(Request $request, string $page): void
    {
        $ip = $request->ip();

        // Skip private/local IPs and common bots
        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return;
        }

        $ua = $request->userAgent() ?? '';
        if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit/i', $ua)) {
            return;
        }

        PageView::create([
            'page'       => $page,
            'ip'         => $ip,
            'user_agent' => substr($ua, 0, 255),
            'referrer'   => $request->header('referer'),
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Anti-spam: quarantine anything that trips a signal — store it flagged
        // (never lost, reviewable in admin) but skip the inbox notification, and
        // still show "success" so bots don't learn they were blocked.
        $isSpam = $this->looksLikeSpam($request, $validated);

        $contactMessage = ContactMessage::create($validated + ['is_spam' => $isSpam]);

        if (! $isSpam) {
            $profile = Profile::first();
            if ($profile && $profile->email) {
                Mail::to($profile->email)->send(new ContactNotification($contactMessage));
            }
        }

        return redirect()->back()->with('contact_success', true);
    }

    /**
     * Layered, no-friction spam heuristics for the contact form.
     * Real visitors never trip these; automated bots reliably do.
     */
    private function looksLikeSpam(Request $request, array $data): bool
    {
        // 1. Honeypot — a hidden field no human ever sees or fills.
        if (filled($request->input('website'))) {
            return true;
        }

        // 2. Time-trap — bots submit near-instantly. Require a few seconds
        //    on the page (and reject absurdly stale/forged timestamps).
        $renderedAt = (int) $request->input('form_ts', 0);
        $elapsed    = time() - $renderedAt;
        if ($renderedAt <= 0 || $elapsed < 3 || $elapsed > 86400) {
            return true;
        }

        // 3. Content signals — this form is for a human to reach out; real
        //    messages almost never carry links. Spam here always does.
        $message = (string) ($data['message'] ?? '');
        $name    = (string) ($data['name'] ?? '');

        $linkCount = preg_match_all('~https?://|www\.|\b[a-z0-9-]+\.(link|top|xyz|shop|club|online|site|casino|bet|win|loan)\b~i', $message . ' ' . $name);
        if ($linkCount >= 1) {
            return true;
        }

        // 4. Common spam keywords seen in these blasts.
        if (preg_match('~\b(jackpot|casino|crypto|viagra|cialis|\$\d[\d,]{3,}|bit\.ly|tinyurl|forex|binary option|sex|porn|escort)\b~i', $message . ' ' . $name)) {
            return true;
        }

        // 5. A name that is itself an email address is a bot tell.
        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }
}
