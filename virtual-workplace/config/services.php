<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'realtime' => [
        // No fallback to APP_KEY on purpose: signing realtime auth tokens
        // with the app's core encryption key would reuse one secret across
        // two unrelated security domains. Must be set explicitly per
        // environment; RealtimeTokenService throws if this is empty.
        'secret' => env('REALTIME_SECRET'),
        'ws_url' => env('REALTIME_WS_URL', 'ws://127.0.0.1:8080'),
    ],

    'livekit' => [
        'host' => env('LIVEKIT_HOST', env('LIVEKIT_URL', 'http://localhost:7880')),
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
    ],

    // Emails granted Super Admin access when a user has no is_super_admin
    // DB flag and no super_admin role membership. Empty by default so an
    // unset env var grants nobody — never hardcode real addresses here.
    'super_admin_emails' => env('SUPER_ADMIN_EMAILS', ''),

    'turn' => [
        'url' => env('TURN_URL'),
        'username' => env('TURN_USERNAME'),
        'credential' => env('TURN_CREDENTIAL'),
    ],

];
