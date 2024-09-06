<?php

declare(strict_types=1);

return [
    'middleware' => [
        'web',
    ],

    'link-middleware' => [
        'web',
        'auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Apple
    |--------------------------------------------------------------------------
    |
    | Apple does not provide an API for their services.
    | It is possible to use CalDav to access apple calendars, however that
    | requires the user to set up an App-specific password. More information can
    | be found at: https://support.apple.com/en-us/HT204397
    */
    //    'apple' => [
    //        'client_id' => env('APPLE_CLIENT_ID'),
    //        'client_secret' => env('APPLE_CLIENT_SECRET'),
    //        'redirect' => env('APPLE_REDIRECT_URI'),
    //    ],

    /*
    |--------------------------------------------------------------------------
    | Google
    |--------------------------------------------------------------------------
    |
    | The credentials for signing in with OAuth on Google.
    | You can configure apps at:
    | https://console.cloud.google.com/apis/credentials/consent
    | More information at: https://developers.google.com/identity/protocols/oauth2
    |
    | Documentation for the APIs can be found at:
    | - Files: https://developers.google.com/drive/api/v3/about-sdk
    | - Mail: https://developers.google.com/gmail/api
    |         https://developers.google.com/gmail/imap
    | - Calendar: https://developers.google.com/calendar/api
    | - Todos: https://developers.google.com/tasks/reference/rest
    | - Contacts: https://developers.google.com/people
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft
    |--------------------------------------------------------------------------
    |
    | The credentials for signing in with OAuth on Microsoft.
    | You can configure apps at:
    | https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade
    |
    | Documentation for the APIs can be found at:
    | - Files: https://docs.microsoft.com/en-us/graph/onedrive-concept-overview
    | - Mail: https://docs.microsoft.com/en-us/graph/outlook-mail-concept-overview
    | - Calendar: https://docs.microsoft.com/en-us/graph/outlook-calendar-concept-overview
    | - Todos: https://docs.microsoft.com/en-us/graph/api/resources/todo-overview
    | - Contacts: https://docs.microsoft.com/en-us/graph/outlook-contacts-concept-overview
    */
    'azure' => [
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI'),
        'tenant' => env('AZURE_TENANT_ID'),
        'logout_url' => 'https://login.microsoftonline.com/'.env('AZURE_TENANT_ID').'/oauth2/v2.0/logout?post_logout_redirect_uri=',
    ],
];
