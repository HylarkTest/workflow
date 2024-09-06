<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\PinItemRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListItemQuery<\App\Models\Pin, \App\Models\Pinboard>
 */
class PinQuery extends FeatureListItemQuery
{
    protected function getCreateDataKeys(): array
    {
        return ['name', 'description', 'image'];
    }

    protected function getUpdateDataKeys(): array
    {
        return ['name', 'description', 'image', 'isFavorite'];
    }

    protected function repository(): PinItemRepository
    {
        return resolve(PinItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'pinboard';
    }

    protected function getItemKey(): string
    {
        return 'pin';
    }

    protected function validateData(Base $base, array $data): void
    {
        if (! $base->accountLimits()->canCreatePins()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
