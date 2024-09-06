<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Image;
use App\Models\Mapping;
use App\Models\Category;
use App\Models\Document;
use MarkupUtils\MarkupType;
use Mappings\Models\CategoryItem;

return [
    'currencies' => [
        'driver' => env('CURRENCIES_DRIVER', 'database'),
        'database' => env('CURRENCIES_DATABASE', 'currencies'),
        'cache' => [
            'key' => env('CURRENCIES_CACHE_KEY', 'currencies'),
            'ttl' => env('CURRENCIES_CACHE_TTL', 60 * 60 * 12),
        ],
        'fixer' => [
            'key' => env('FIXER_API_KEY'),
        ],
    ],

    'locations' => [
        'database' => env('LOCATIONS_DATABASE', 'locations'),
    ],

    'models' => [
        'mapping' => Mapping::class,
        'item' => Item::class,
        'document' => Document::class,
        'image' => Image::class,
        'category' => Category::class,
        'category_item' => CategoryItem::class,
    ],

    'fields' => [
        'formatted' => [
            'format' => MarkupType::DELTA,
        ],
    ],
    'filesystems' => [
        'images' => 'images',
        'documents' => 'documents',
    ],
];
