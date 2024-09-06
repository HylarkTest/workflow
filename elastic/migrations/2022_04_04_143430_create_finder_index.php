<?php

declare(strict_types=1);

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateFinderIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::createIfNotExistsRaw(config('finder.prefix').config('finder.index'), [
            'dynamic' => false,
            '_routing' => [
                'required' => true,
            ],
            'properties' => [
                'id' => [
                    'type' => 'keyword',
                ],
                'primary' => [
                    'properties' => [
                        'text' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                        'map' => [
                            'type' => 'keyword',
                            'store' => false,
                        ],
                    ],
                ],
                'secondary' => [
                    'properties' => [
                        'text' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                        'map' => [
                            'type' => 'keyword',
                            'store' => false,
                        ],
                    ],
                ],
                'space_id' => [
                    'type' => 'keyword',
                ],
                'created_at' => [
                    'type' => 'date',
                ],
                'updated_at' => [
                    'type' => 'date',
                ],
                '__typename' => [
                    'type' => 'keyword',
                ],
            ],
        ]);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists(config('finder.index'));
    }
}
