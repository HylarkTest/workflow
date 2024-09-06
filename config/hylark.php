<?php

declare(strict_types=1);

use App\Models\Pin;
use App\Models\Item;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Event;
use App\Models\Document;

return [
    /*
    |--------------------------------------------------------------------------
    | Relationship map
    |--------------------------------------------------------------------------
    | ** DO NOT CHANGE **
    |
    | Due to the nature of Laravel polymorphic relationships, there needs to be
    | a "parent" to the relationship. In Hylark we want everything to be
    | relatable to everything else, so there is a morph pivot table for each
    | relatable model, however it is arbitrary which model is parent to the
    | the other. However it does need to be consistent to define the
    | relationships.
    | It should be possible to have one two way morph relationship, but that is
    | surprisingly challenging to do with Laravel. This has its downsides but
    | makes for easier code.
    | This array defines the "direction" of relationships between the different
    | models.
    */
    'relationship_map' => [
        [Note::class, Item::class, 'notable'],
        [Todo::class, Item::class, 'todoable'],
        [Event::class, Item::class, 'eventable'],
        [Pin::class, Item::class, 'pinable'],
        [Link::class, Item::class, 'linkable'],
        [Document::class, Item::class, 'attachable'],

        [Note::class, Todo::class, 'notable'],
        [Note::class, Event::class, 'notable'],
        [Todo::class, Event::class, 'todoable'],
        [Todo::class, Pin::class, 'todoable'],
        [Event::class, Pin::class, 'eventable'],
        [Event::class, Link::class, 'eventable'],
        [Pin::class, Link::class, 'pinable'],
        [Pin::class, Document::class, 'pinable'],
        [Link::class, Document::class, 'linkable'],
        [Link::class, Note::class, 'linkable'],
        [Document::class, Note::class, 'attachable'],
        [Document::class, Todo::class, 'attachable'],
    ],

    'admin_emails' => env('ADMIN_EMAILS')
        ? explode(',', env('ADMIN_EMAILS'))
        : [],

    'support' => [
        'cache' => [
            'enabled' => env('SUPPORT_CACHE_ENABLED', env('APP_ENV') === 'production'),
            'key' => 'support',
            'ttl' => 60 * 60 * 24,
        ],
        'database' => env('SUPPORT_DATABASE', env('DB_CONNECTION')),
    ],
    'cors_proxy_url' => env('CORS_PROXY_URL'),

    'production_url' => env('PRODUCTION_URL', 'https://app.hylark.com'),

    'mobile' => [
        'client_id' => env('MOBILE_APP_CLIENT_ID'),
    ],

    'imports' => [
        'disk' => env('IMPORTS_DISK', 'tmp'),
        'queue' => env('IMPORTS_QUEUE', 'imports'),
        'revert_queue' => env('IMPORTS_REVERT_QUEUE', env('IMPORTS_QUEUE', 'imports')),
        'chunk_size' => env('IMPORTS_CHUNK_SIZE', 100),
        'revert_chunk_size' => env('IMPORTS_REVERT_CHUNK_SIZE', env('IMPORTS_CHUNK_SIZE', 100)),
    ],
];
