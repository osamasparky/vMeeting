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
        'secret' => env('REALTIME_SECRET', env('APP_KEY', 'base64:9fj2ZRPjCy3ClL13gPaYCv9gl8GsE8APwzVK8EceIRM=')),
        'ws_url' => env('REALTIME_WS_URL'),
    ],

    'livekit' => [
        'host' => env('LIVEKIT_HOST', env('LIVEKIT_URL', 'wss://nextspace.munazzah.com/livekit')),
        'api_key' => env('LIVEKIT_API_KEY', 'devkey'),
        'api_secret' => env('LIVEKIT_API_SECRET', 'secret_livekit_key_virtual_workplace_2026'),
    ],

    'turn' => [
        'url' => env('TURN_URL', 'turn:173.212.248.192:3478'),
        'username' => env('TURN_USERNAME', 'vw_turn_user'),
        'credential' => env('TURN_CREDENTIAL', 'vw_turn_password_2026'),
    ],

];
