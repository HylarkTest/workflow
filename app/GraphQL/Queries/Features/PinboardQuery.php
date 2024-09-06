<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\PinboardRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\Pinboard>
 */
class PinboardQuery extends FeatureListQuery
{
    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultPinboards();
    }

    protected function repository(): PinboardRepository
    {
        return resolve(PinboardRepository::class);
    }

    protected function getListKey(): string
    {
        return 'pinboard';
    }
}
