<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\LinkItemRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListItemQuery<\App\Models\Link, \App\Models\LinkList>
 */
class LinkQuery extends FeatureListItemQuery
{
    protected function getCreateDataKeys(): array
    {
        return ['name', 'description', 'url'];
    }

    protected function getUpdateDataKeys(): array
    {
        return ['name', 'description', 'url', 'isFavorite'];
    }

    protected function repository(): LinkItemRepository
    {
        return resolve(LinkItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'linkList';
    }

    protected function getItemKey(): string
    {
        return 'link';
    }

    protected function validateData(Base $base, array $data): void
    {
        if (! $base->accountLimits()->canCreateLinks()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
