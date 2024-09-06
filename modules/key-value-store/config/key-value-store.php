<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Store Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard the store will use while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Store Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | Here you may specify which prefix the store will assign to all the routes
    | that it registers with the application. If necessary, you may change
    | subdomain under which all of the store routes will be available.
    |
    */

    'domain' => null,

    'prefix' => 'store',

    /*
    |--------------------------------------------------------------------------
    | Store Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify the middleware that sits behind every route in the
    | key value store. The authenticate middleware should always be there
    | to ensure the keys are scoped to the authenticated user.
    */

    'middleware' => [
        'web',
        \Illuminate\Auth\Middleware\Authenticate::class,
    ],

    'key-prefix' => 'store',

    /*
    |--------------------------------------------------------------------------
    | Store driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify which redis driver the store will use when storing
    | and retrieving keys. This value should correspond with one of your
    | stores that is already present in your "database" configuration
    | file. A value of null corresponds to the default redis.
    */

    'store' => null,

    /*
    |--------------------------------------------------------------------------
    | Store TTL
    |--------------------------------------------------------------------------
    |
    | Here you may specify how long (in seconds) the values can be held in the
    | cache. Setting this to null will store the values in the cache
    | indefinitely.
    */

    'max-ttl' => 30 * 24 * 60 * 60,

    /*
    |--------------------------------------------------------------------------
    | Store Maximum Value Size
    |--------------------------------------------------------------------------
    |
    | Here you may specify the maximum size of each value stored in the cache
    | in bytes.
    */

    'max-bytes' => 100 * 1024,

    /*
    |--------------------------------------------------------------------------
    | Store Maximum Number of Keys
    |--------------------------------------------------------------------------
    |
    | Here you may specify the maximum number of keys each user can have stored.
    */

    'max-keys' => 1000,
];
