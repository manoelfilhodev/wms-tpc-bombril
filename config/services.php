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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'allowed_domains' => array_filter(array_map('trim', explode(',', env('MICROSOFT_ALLOWED_DOMAINS', '')))),
        'auto_provision_users' => env('MICROSOFT_AUTO_PROVISION_USERS', false),
        'default_role' => env('MICROSOFT_DEFAULT_ROLE', 'OPERADOR'),
        'default_unidade_id' => env('MICROSOFT_DEFAULT_UNIDADE_ID'),
        'post_logout_redirect_uri' => env('MICROSOFT_POST_LOGOUT_REDIRECT_URI', env('APP_URL') . '/login'),
    ],

    'graph' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
    ],

];
