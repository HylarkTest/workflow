<?php

declare(strict_types=1);

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateItemsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::createIfNotExistsRaw(config('scout.prefix').'items', [
            'dynamic' => false,
            '_routing' => [
                'required' => true,
            ],
            'properties' => [
                'id' => ['type' => 'keyword'],
                'type' => ['type' => 'keyword'],
                'name' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'normalizer' => 'lowercase',
                        ],
                    ],
                ],
                'text_fields' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'field' => ['type' => 'keyword'],
                    ],
                ],
                'emails' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => ['type' => 'keyword'],
                    ],
                ],
                'keyword_fields' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => ['type' => 'keyword'],
                        'sortable_value' => ['type' => 'keyword'],
                        'field' => ['type' => 'keyword'],
                    ],
                ],
                'date_fields' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
                        ],
                        'field' => ['type' => 'keyword'],
                    ],
                ],
                'boolean_fields' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => ['type' => 'boolean'],
                        'sortable_value' => ['type' => 'keyword'],
                        'field' => ['type' => 'keyword'],
                    ],
                ],
                'integer_fields' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => ['type' => 'integer'],
                        'field' => ['type' => 'keyword'],
                    ],
                ],
                'childRelations' => [
                    'type' => 'nested',
                    'properties' => [
                        'relation_id' => ['type' => 'keyword'],
                        'item_id' => ['type' => 'keyword'],
                    ],
                ],
                'parentRelations' => [
                    'type' => 'nested',
                    'properties' => [
                        'relation_id' => ['type' => 'keyword'],
                        'item_id' => ['type' => 'keyword'],
                    ],
                ],
                'markers' => [
                    'type' => 'nested',
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'marker_group_id' => ['type' => 'keyword'],
                        'context' => ['type' => 'keyword'],
                    ],
                ],
                'priority' => ['type' => 'keyword'],
                'favorited_at' => ['type' => 'date'],
                'start_at' => ['type' => 'date'],
                'due_by' => ['type' => 'date'],
                'completed_at' => ['type' => 'date'],
                'created_at' => ['type' => 'date'],
                'updated_at' => ['type' => 'date'],
                'mapping_id' => ['type' => 'keyword'],
                'features' => ['type' => 'keyword'],
                'space_id' => ['type' => 'keyword'],
            ],
        ], [
            'analysis' => [
                'normalizer' => [
                    'lowercase' => [
                        'type' => 'custom',
                        'char_filter' => [],
                        'filter' => ['lowercase'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('items');
    }
}
