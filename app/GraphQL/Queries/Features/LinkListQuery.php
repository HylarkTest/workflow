<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\LinkListRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\LinkList>
 */
class LinkListQuery extends FeatureListQuery
{
    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultLinkLists();
    }

    protected function repository(): LinkListRepository
    {
        return resolve(LinkListRepository::class);
    }

    protected function getListKey(): string
    {
        return 'linkList';
    }
}
