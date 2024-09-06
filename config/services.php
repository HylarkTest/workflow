<?php

declare(strict_types=1);

$hostname = env('APP_ENV') === 'production' ? trim(file_get_contents('/etc/hostname')) : null;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, AWS and more. This file provides the de facto
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    'azure' => [
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI'),
        'tenant' => env('AZURE_TENANT_ID', 'common'),
        'logout_url' => 'https://login.microsoftonline.com/'.env('AZURE_TENANT_ID').'/oauth2/v2.0/logout?post_logout_redirect_uri=',
    ],

    'forge' => [
        'api_key' => env('FORGE_API_KEY'),
        'server_id' => match ($hostname) {
            'hyk-web-01' => env('FORGE_SERVER_1_ID'),
            'hyk-web-02' => env('FORGE_SERVER_2_ID'),
            default => env('FORGE_SERVER_ID'),
        },
        'site_id' => match ($hostname) {
            'hyk-web-01' => env('FORGE_SITE_1_ID'),
            'hyk-web-02' => env('FORGE_SITE_2_ID'),
            default => env('FORGE_SITE_ID'),
        },
        'site' => env('FORGE_SITE', env('DOMAIN')),
        'blue_port' => env('FORGE_BLUE_PORT', 8001),
        'green_port' => env('FORGE_GREEN_PORT', 8002),
        'nginx_switch_recipe_id' => env('FORGE_NGINX_SWITCH_RECIPE_ID'),
    ],

    'freshdesk' => [
        'api_key' => env('FRESHDESK_API_KEY', ''),
        'url' => env('FRESHDESK_URL', ''),
        'product_id' => env('FRESHDESK_PRODUCT_ID'),
        'group_id' => env('FRESHDESK_GROUP_ID'),
        'portal_id' => env('FRESHDESK_PORTAL_ID'),
    ],

    'search_api' => [
        'url' => env('SEARCH_API_URL', 'https://www.googleapis.com/customsearch/v1'),
        'api_key' => env('GOOGLE_SEARCH_API_KEY'),
        'engine_id' => env('GOOGLE_SEARCH_ENGINE_ID'),
        'results_count' => env('GOOGLE_SEARCH_RESULTS_COUNT', 10),
    ],
];
