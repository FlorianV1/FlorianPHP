<?php

declare(strict_types=1);

/*
| Hub-side Bugsnag configuration. Named `bugsnag-hub` because
| `bugsnag/bugsnag-laravel` — which reports THIS app's own exceptions —
| publishes its own `config/bugsnag.php`. The two are unrelated: that one
| holds a notifier API key, these keys hold a personal auth token that can
| read the Data Access API.
*/

return [

    /*
    | Hub-side Bugsnag integration. One agency-wide personal auth token lets
    | the dashboard query the Data Access API for every client project, so
    | client sites never need Bugsnag credentials of their own and error
    | counts work whether or not a site is enrolled in the bridge.
    |
    | Create the token under Bugsnag > Settings > My account > Personal auth
    | tokens. It is a different credential from a project's notifier API key
    | (the key stored per website), which cannot read the Data Access API.
    | Null keeps the integration off.
    */

    'auth_token' => env('BUGSNAG_AUTH_TOKEN'),

    /*
    | The organization whose projects are linked. Left blank, the first
    | organization the token can see is used — correct for a single-org
    | agency account, which is the common case.
    */
    'organization_id' => env('BUGSNAG_ORGANIZATION_ID'),

    'api_url' => env('BUGSNAG_API_URL', 'https://api.bugsnag.com'),

    'timeout_seconds' => (int) env('BUGSNAG_TIMEOUT', 10),

    /*
    | Safety valve for the paginated project listing, so a malformed Link
    | header can never spin the linker forever.
    */
    'max_project_pages' => (int) env('BUGSNAG_MAX_PROJECT_PAGES', 20),

];
