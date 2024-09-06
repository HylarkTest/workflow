<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @extends FeatureItemRepository<\App\Models\Link, \App\Models\LinkList>
 */
class LinkItemRepository extends FeatureItemRepository
{
    protected function getListOrderByField(): string
    {
        return 'linkList';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Link>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->links()->getQuery();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\LinkList>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->linkLists()->getQuery();
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::LINKS;
    }
}
