<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => env('PUBLIC_FILESYSTEM_DISK', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_PUBLIC_BUCKET'),
            'url' => env('APP_ENV') !== 'production'
                ? env('AWS_URL').'/'.env('AWS_PUBLIC_BUCKET')
                : env('AWS_PROTOCOL').env('AWS_PUBLIC_BUCKET').'.'.env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],

        // This has the same configuration as the `public` filesystem but is
        // included in the tenancy config so any documents added to this
        // disk will be scoped to the appropriate base.
        'images' => [
            'driver' => env('PUBLIC_FILESYSTEM_DISK', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_IMAGES_BUCKET'),
            'url' => env('APP_ENV') !== 'production'
                ? env('AWS_URL').'/'.env('AWS_IMAGES_BUCKET')
                : env('AWS_IMAGES_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],

        'documents' => [
            'driver' => env('DOCUMENTS_FILESYSTEM_DISK', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_DOCUMENTS_BUCKET'),
            'url' => env('APP_ENV') !== 'production'
                ? env('AWS_URL').'/'.env('AWS_DOCUMENTS_BUCKET')
                : env('AWS_DOCUMENTS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_DOCUMENTS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],

        'resources' => [
            'driver' => env('RESOURCES_FILESYSTEM_DISK', env('PUBLIC_FILESYSTEM_DISK', 's3')),
            'key' => env('RESOURCES_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('RESOURCES_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('RESOURCES_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
            'bucket' => env('AWS_RESOURCES_BUCKET', env('AWS_IMAGES_BUCKET')),
            'url' => env('RESOURCES_AWS_URL', env('APP_ENV') !== 'production'
                ? env('AWS_URL').'/'.env('AWS_IMAGES_BUCKET')
                : env('AWS_PROTOCOL').env('AWS_IMAGES_BUCKET').'.'.env('AWS_URL')),
            'endpoint' => env('RESOURCES_AWS_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],

        'tmp' => [
            'driver' => env('TMP_FILESYSTEM_DISK', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_TMP_BUCKET'),
            'url' => env('APP_ENV') !== 'production'
                ? env('AWS_URL').'/'.env('AWS_TMP_BUCKET')
                : env('AWS_TMP_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'cache' => [
                'store' => env('CACHE_DRIVER', 'file'),
                'expire' => 600,
                'prefix' => 's3:',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
