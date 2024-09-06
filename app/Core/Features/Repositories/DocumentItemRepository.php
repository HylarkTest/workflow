<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Document;
use Illuminate\Support\Collection;
use App\Models\Contracts\FeatureList;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @extends FeatureItemRepository<\App\Models\Document, \App\Models\Drive>
 */
class DocumentItemRepository extends FeatureItemRepository
{
    protected function getListOrderByField(): string
    {
        return 'drive';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Document>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->documents()->getQuery();
    }

    protected function getSearchFields(): array
    {
        return ['filename'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Drive>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->drives()->getQuery();
    }

    /**
     * @param  \App\Models\Drive  $list
     * @return \App\Models\Document
     */
    protected function createFeatureItemFromAttributes(FeatureList $list, array $data): FeatureListItem
    {
        $attributes = [
            'drive_id' => $list->id,
        ];
        if (isset($data['filename'])) {
            $attributes['filename'] = $data['filename'];
        }

        /** @var \App\Models\Document $document */
        $document = Document::createFromFile($data['file'], $attributes);

        return $document;
    }

    /**
     * @param  \App\Models\Document  $item
     * @return \App\Models\Document
     */
    protected function duplicateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        if (! isset($data['drive_id'])) {
            $data['drive_id'] = $item->drive_id ?? null;
        }

        /** @var \App\Models\Document $document */
        $document = Document::createFromItem($item, $data);

        return $document;
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::DOCUMENTS;
    }

    protected function getGroupHeaders(string $group, array $filters = []): Collection
    {
        if ($group === 'EXTENSION') {
            /** @phpstan-ignore-next-line $base is not null here */
            return $this->getItemQuery($this->base)
                ->groupBy('extension')
                ->pluck('extension')
                ->sort();
        }

        return parent::getGroupHeaders($group, $filters);
    }
}
