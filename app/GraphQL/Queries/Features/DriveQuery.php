<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\DriveRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\Drive>
 */
class DriveQuery extends FeatureListQuery
{
    protected function repository(): DriveRepository
    {
        return resolve(DriveRepository::class);
    }

    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultDrives();
    }

    protected function getListKey(): string
    {
        return 'drive';
    }
}
