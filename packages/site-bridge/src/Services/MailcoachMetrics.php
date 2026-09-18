<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Services;

use Agency\SiteBridge\Support\Section;

/**
 * Reports MailCoach subscriber/list/campaign counts from the models
 * installed in this app, when the package is present.
 */
final class MailcoachMetrics
{
    private const SUBSCRIBER_MODEL = '\Spatie\Mailcoach\Domain\Audience\Models\Subscriber';

    private const LIST_MODEL = '\Spatie\Mailcoach\Domain\Audience\Models\EmailList';

    private const CAMPAIGN_MODEL = '\Spatie\Mailcoach\Domain\Campaign\Models\Campaign';

    public function metrics(): Section
    {
        if (config('site-bridge.mailcoach.enabled') !== true) {
            return Section::unavailable('mailcoach metrics disabled');
        }

        $subscriberModel = self::SUBSCRIBER_MODEL;
        $listModel = self::LIST_MODEL;
        $campaignModel = self::CAMPAIGN_MODEL;

        if (! class_exists($subscriberModel)) {
            return Section::unavailable('spatie/mailcoach not installed');
        }

        return Section::attempt(fn (): Section => Section::ok([
            'subscribers' => $subscriberModel::query()->count(),
            'lists' => class_exists($listModel) ? $listModel::query()->count() : null,
            'campaigns' => class_exists($campaignModel) ? $campaignModel::query()->count() : null,
        ]));
    }
}
