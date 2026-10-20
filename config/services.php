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

    'meta' => [
        'access_token' => env('META_ADS_ACCESS_TOKEN'),
        'ad_account_id' => env('META_AD_ACCOUNT_ID', 'act_1922369221497688'),
    ],

    'sleekflow' => [
        'key' => env('SLEEKFLOW_API_KEY'),
        'base_url' => env('SLEEKFLOW_BASE_URL', 'https://sleekflow-core-app-seas-production.azurewebsites.net/api'),
        // Endpoint Daily Analytics: GET {base_url}/analytic?startDate=Y-m-d&endDate=Y-m-d
        // (respons: from, to, dailyAnalytics[]). Bisa di-override via .env SLEEKFLOW_ANALYTICS_URL
        'analytics_url' => env('SLEEKFLOW_ANALYTICS_URL')
            ?: rtrim(env('SLEEKFLOW_BASE_URL', 'https://sleekflow-core-app-seas-production.azurewebsites.net/api'), '/') . '/analytic',
        // Header autentikasi untuk endpoint analytics (default sama dengan endpoint contact)
        'analytics_auth_header' => env('SLEEKFLOW_ANALYTICS_AUTH_HEADER', 'X-Sleekflow-Api-Key'),
    ],

    'dashboard' => [
        'key' => env('DASHBOARD_API_KEY'),
        'base_url' => env('DASHBOARD_API_URL'),
    ],

];
