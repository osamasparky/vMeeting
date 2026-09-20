<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Laravel's HandleCors middleware reads this file. Until now it didn't
    | exist, so CORS was implicitly off — same-origin only. This file makes
    | that stance explicit and configurable without loosening it: the
    | allow-list defaults to empty (no browser origin may call the API
    | cross-origin). Native/mobile clients aren't subject to CORS and are
    | unaffected either way.
    |
    | To let a separate web front-end call the API, list its exact origin(s)
    | in CORS_ALLOWED_ORIGINS, comma-separated, e.g.
    |   CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
    | Never use "*" together with credentials.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),

];
