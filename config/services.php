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

    'muhammadiyah_id' => [
        'client_id' => env('MUHAMMADIYAH_ID_CLIENT_ID'),
        'client_secret' => env('MUHAMMADIYAH_ID_CLIENT_SECRET'),
        'redirect_uri' => env('MUHAMMADIYAH_ID_REDIRECT_URI', env('APP_URL').'/auth/muhammadiyah/callback'),
        'base_url' => rtrim(env('MUHAMMADIYAH_ID_BASE_URL', 'https://sso.muhammadiyah.id'), '/'),
        'api_url' => rtrim(env('MUHAMMADIYAH_ID_API_URL', 'https://sso.muhammadiyah.id/api'), '/'),
        'scope' => env('MUHAMMADIYAH_ID_SCOPE', 'user-info'),
        'require_pre_registered' => env('MUHAMMADIYAH_ID_REQUIRE_PRE_REGISTERED', true),
    ],

];
