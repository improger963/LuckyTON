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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'verify_data' => env('TELEGRAM_VERIFY_DATA', true),
    ],

    'payment_gateway' => [
        'url' => env('PAYMENT_GATEWAY_URL'),
        'token' => env('PAYMENT_GATEWAY_TOKEN'),
        'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30),
    ],

    'coinpayments' => [
        'url' => env('COINPAYMENTS_URL'),
        'token' => env('COINPAYMENTS_TOKEN'),
        'timeout' => env('COINPAYMENTS_TIMEOUT', 30),
    ],

    'webhook' => [
        'secret' => env('WEBHOOK_SECRET'),
    ],
];
