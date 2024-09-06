<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\NotebookRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\Notebook>
 */
class NotebookQuery extends FeatureListQuery
{
    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultNotebooks();
    }

    protected function repository(): NotebookRepository
    {
        return resolve(NotebookRepository::class);
    }

    protected function getListKey(): string
    {
        return 'notebook';
    }
}
