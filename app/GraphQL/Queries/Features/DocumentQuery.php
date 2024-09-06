<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\DocumentItemRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListItemQuery<\App\Models\Document, \App\Models\Drive>
 */
class DocumentQuery extends FeatureListItemQuery
{
    public static string $itemQueryParams = <<<'GRAPHQL'
        fileTypes: [FileType!]
    GRAPHQL;

    protected function repository(): DocumentItemRepository
    {
        return resolve(DocumentItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'drive';
    }

    protected function getCreateDataKeys(): array
    {
        return ['filename', 'file'];
    }

    protected function getUpdateDataKeys(): array
    {
        return ['filename', 'isFavorite'];
    }

    protected function getItemKey(): string
    {
        return 'document';
    }

    protected function validateData(Base $base, array $data): void
    {
        if (isset($data['file']) && ! $base->accountLimits()->canUploadFile($data['file'])) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
