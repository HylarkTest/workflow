<?php

declare(strict_types=1);

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateSupportArticlesIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::createIfNotExistsRaw(config('scout.prefix').'support_articles', [
            'dynamic' => false,
            'properties' => [
                'id' => ['type' => 'keyword'],
                'title' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => ['type' => 'keyword'],
                    ],
                ],
                'friendly_url' => ['type' => 'keyword'],
                'content' => ['type' => 'text'],
                'topics' => [
                    'type' => 'nested',
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'name' => ['type' => 'keyword'],
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
        Index::dropIfExists('support_articles');
    }
}
