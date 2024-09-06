<?php

declare(strict_types=1);

namespace App\DataIntegrity;

use App\Models\Page;
use Markers\Models\Marker;
use App\Models\SavedFilter;
use Mappings\Models\Mapping;
use Mappings\Models\CategoryItem;
use Mappings\Core\Mappings\Fields\FieldType;

/**
 * @phpstan-import-type FieldFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 * @phpstan-import-type MarkerFilterCollection from \App\Core\Mappings\Repositories\ItemFilter
 */
class SavedFilterIntegrity extends DataIntegrity
{
    public array $events = [
        'eloquent.updated: '.Mapping::class => 'onMappingUpdated',
        'eloquent.updated: '.\App\Models\Mapping::class => 'onMappingUpdated',
        'eloquent.deleted: '.Marker::class => 'onMarkerDeleted',
        'eloquent.deleted: '.\App\Models\Marker::class => 'onMarkerDeleted',
        'eloquent.deleted: '.CategoryItem::class => 'onCategoryItemDeleted',
    ];

    public function onMappingUpdated(Mapping $mapping): void
    {
        if ($mapping->wasChanged('marker_groups', 'fields')) {
            $mapping = (new \App\Models\Mapping)->newFromBuilder($mapping->getAttributes());
            $mapping->pages
                ->load('savedFilters')
                ->each(function (Page $page) use ($mapping) {
                    $page->savedFilters
                        ->each(function (SavedFilter $savedFilter) use ($mapping) {
                            /** @var array{ markers?: MarkerFilterCollection, fields?: FieldFilterCollection } $filters */
                            $filters = $savedFilter->filters;
                            if (isset($filters['fields'])) {
                                $filters['fields'] = collect($filters['fields'])
                                    ->filter(function ($fieldFilter) use ($mapping) {
                                        $field = $mapping->fields->find($fieldFilter['fieldId']);
                                        if (! $field) {
                                            return false;
                                        }
                                        if ($field->type()->is(FieldType::SELECT())) {
                                            $options = $field->option('valueOptions');
                                            if (! array_key_exists(json_decode($fieldFilter['match']), $options)) {
                                                return false;
                                            }
                                        }

                                        return true;
                                    })
                                    ->values()
                                    ->all();
                            }
                            if (isset($filters['markers'])) {
                                $filters['markers'] = collect($filters['markers'])
                                    ->filter(fn ($marker) => ($marker['context'] ?? null) ? (bool) $mapping->markerGroups?->contains('id', $marker['context']) : true)
                                    ->values()
                                    ->all();
                            }
                            $group = $savedFilter->group;
                            if ($group) {
                                if (str_starts_with($group, 'marker:')) {
                                    /** @var string $markerGroupId */
                                    $markerGroupId = substr($group, 7);
                                    if (! $mapping->markerGroups?->contains('id', $markerGroupId)) {
                                        $savedFilter->group = null;
                                    }
                                } elseif (str_starts_with($group, 'field:')) {
                                    /** @var string $fieldId */
                                    $fieldId = substr($group, 6);
                                    if (! $mapping->fields->find($fieldId)) {
                                        $savedFilter->group = null;
                                    }
                                }
                            }
                            $orderBy = $savedFilter->order_by;
                            if ($orderBy) {
                                $savedFilter->order_by = collect($orderBy)
                                    ->filter(function ($orderBy) use ($mapping) {
                                        $field = $orderBy['field'];
                                        if (str_starts_with($field, 'field:')) {
                                            $fieldId = substr($field, 6);
                                            if (! $mapping->fields->find($fieldId)) {
                                                return false;
                                            }
                                        }

                                        return true;
                                    })
                                    ->values()
                                    ->all();
                            }
                            $savedFilter->filters = $filters;
                            $savedFilter->save();
                        });
                });
        }
    }

    public function onMarkerDeleted(Marker $marker): void
    {
        $marker = (new \App\Models\Marker)->forceFill($marker->getAttributes());
        $marker->base->savedFilters
            /** @phpstan-ignore-next-line This should not be an error */
            ->each(function (SavedFilter $savedFilter) use ($marker) {
                /** @var array{ markers?: MarkerFilterCollection }  $filters */
                $filters = $savedFilter->filters;
                if (isset($filters['markers'])) {
                    $filters['markers'] = collect($filters['markers'])
                        ->filter(fn ($filter) => $filter['markerId'] !== $marker->global_id)
                        ->values()
                        ->all();
                }
                $savedFilter->filters = $filters;
                $savedFilter->save();
            });
    }

    public function onCategoryItemDeleted(CategoryItem $categoryItem): void
    {
        /** @var \App\Models\Category $category */
        $category = $categoryItem->category;
        $category->base->savedFilters
            /** @phpstan-ignore-next-line This should not be an error */
            ->each(function (SavedFilter $savedFilter) use ($categoryItem) {
                /** @var array{ fields?: FieldFilterCollection }  $filters */
                $filters = $savedFilter->filters;
                if (isset($filters['fields'])) {
                    $filters['fields'] = collect($filters['fields'])
                        ->filter(fn ($filter) => json_decode($filter['match']) !== $categoryItem->global_id)
                        ->values()
                        ->all();
                }
                $savedFilter->filters = $filters;
                $savedFilter->save();
            });
    }
}
