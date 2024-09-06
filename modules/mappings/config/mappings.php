<?php

declare(strict_types=1);

return [
    'currencies' => [
        'driver' => env('CURRENCIES_DRIVER', 'database'),
        'fixer' => [
            'key' => env('FIXER_API_KEY'),
        ],
    ],

    'models' => [
        'mapping' => \Mappings\Models\Mapping::class,
        'item' => \Mappings\Models\Item::class,
        'document' => \Mappings\Models\Document::class,
        'image' => \Mappings\Models\Image::class,
        'category' => \Mappings\Models\Category::class,
        'category_item' => \Mappings\Models\CategoryItem::class,
    ],

    'fields' => [
        'formatted' => [
            'format' => \MarkupUtils\MarkupType::DELTA,
        ],
    ],

    'filesystems' => [
        'images' => 'images',
        'documents' => 'documents',
    ],
];
