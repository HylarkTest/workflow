<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using the Finder. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "elastic", "null"
    |
    */

    'driver' => env('FINDER_DRIVER', 'elastic'),

    /*
    |--------------------------------------------------------------------------
    | Default Index
    |--------------------------------------------------------------------------
    |
    | This option controls the default index that should be searched and
    | updated when using the global search. This can be overridden on
    | a per model basis and for each query.
    |
    */

    'index' => env('FINDER_INDEX', 'finder'),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('FINDER_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Per Page
    |--------------------------------------------------------------------------
    |
    | Unlike with the query builder and scout, the pagination count cannot be
    | defined on a per model basis, instead you can set the default
    | pagination count here.
    |
    */

    'per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Searchable Models
    |--------------------------------------------------------------------------
    |
    | This option is where you define which models should be indexed into the
    | global search engine. Each of the models should use the
    | `GloballySearchable` trait so that changes are indexed.
    | The models can be grouped by the index name which will be referenced
    | when indexing and retrieving the models.
    |
    */

    'models' => [
        env('FINDER_INDEX', 'finder') => [
            //
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('FINDER_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Results Collection
    |--------------------------------------------------------------------------
    |
    | As the finder can return multiple models it cannot use the eloquent
    | collection class, instead Finder ships with a custom collection
    | class. You can override that here.
    |
    */
    'collection' => \Finder\Core\FinderCollection::class,
];
