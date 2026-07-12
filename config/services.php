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

    // WooCommerce (cavathanquoc.com) — đồng bộ đơn hàng online. (key nhập thẳng theo yêu cầu)
    'woocommerce' => [
        'url' => 'https://cavathanquoc.com',
        'key' => 'ck_0074a1c148ebf62553872160bfec830b581145d0',
        'secret' => 'cs_0e2f1dea5a383104fccd526cd0ec80756d9d3d21',
        // Chỉ đồng bộ đơn Mail tạo TỪ mốc này trở đi (không kéo đơn cũ về).
        // Có thể ghi đè bằng cấu hình DB 'mail_sync_since'.
        'sync_since' => '2026-07-12',
    ],

];
