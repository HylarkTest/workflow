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
                'createdAt' => [
                    'type' => 'date',
                ],
                'updatedAt' => [
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
