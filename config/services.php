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

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway (notifikasi otomatis ke pendaki)
    |--------------------------------------------------------------------------
    | driver   : log (default, tidak mengirim — hanya dicatat), fonnte, wablas, webhook
    | endpoint : URL API gateway (fonnte punya default; wablas pakai domain server Anda)
    | token    : API key / device token dari penyedia gateway
    | auto_send_on_paid : kirim otomatis saat kasir mengonfirmasi LUNAS
    */
    'whatsapp' => [
        'driver' => env('WA_GATEWAY_DRIVER', 'log'),
        'endpoint' => env('WA_GATEWAY_ENDPOINT'),
        'token' => env('WA_GATEWAY_TOKEN'),
        'secret' => env('WA_GATEWAY_SECRET'),
        'timeout' => (int) env('WA_GATEWAY_TIMEOUT', 10),
        'auto_send_on_paid' => (bool) env('WA_AUTO_SEND_ON_PAID', true),
    ],

];
