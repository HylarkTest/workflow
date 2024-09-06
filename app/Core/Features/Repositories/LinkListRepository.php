<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends FeatureListRepository<\App\Models\LinkList>
 */
class LinkListRepository extends FeatureListRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\LinkList>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->linkLists()->getQuery();
    }
}
