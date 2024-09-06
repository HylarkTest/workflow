<?php

declare(strict_types=1);

use App\Core\IPLocation\Position;
use App\Core\IPLocation\Drivers\IpApi;
use App\Core\IPLocation\Drivers\IpInfo;

return [
    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The default driver you would like to use for location retrieval.
    |
    */

    'driver' => env('IP_LOCATION_DRIVER', IpApi::class),

    /*
    |--------------------------------------------------------------------------
    | Driver Fallbacks
    |--------------------------------------------------------------------------
    |
    | The drivers you want to use to retrieve the users location
    | if the above selected driver is unavailable.
    |
    | These will be called upon in order (first to last).
    |
    */

    'fallbacks' => [
        IpInfo::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    |
    | Here you may configure the position instance that is created
    | and returned from the above drivers. The instance you
    | create must extend the built-in Position class.
    |
    */

    'position' => Position::class,

    /*
    |--------------------------------------------------------------------------
    | IP API Pro Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the IP API Pro driver.
    |
    */

    'ip_api' => [
        'token' => env('IP_API_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IPInfo Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the IPInfo driver.
    |
    */

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IPData Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the IPData driver.
    |
    */

    'ipdata' => [
        'token' => env('IPDATA_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Localhost Testing
    |--------------------------------------------------------------------------
    |
    | If your running your website locally and want to test different
    | IP addresses to see location detection, set 'enabled' to true.
    |
    | The testing IP address is a Google host in the United-States.
    |
    */

    'testing' => [
        'enabled' => env('LOCATION_TESTING', true),
        'ip' => '66.102.0.0',
    ],

    'cache' => [
        'enabled' => env('IP_LOCATION_CACHE_ENABLED', true),
        'ttl' => env('IP_LOCATION_CACHE_TTL', 604_800), // Cache for one week
        'key' => env('IP_LOCATION_CACHE_KEY', 'ip-location'),
    ],
];
