<?php

declare(strict_types=1);

namespace App\DataIntegrity;

use Mappings\Models\Mapping;
use Mappings\Models\Category;
use Markers\Models\MarkerGroup;
use Mappings\Events\AttributeItemRemoving;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Mappings\Core\Mappings\Fields\Types\MultiField;
use Mappings\Core\Mappings\Fields\Types\CategoryField;
use Mappings\Core\Mappings\Relationships\Relationship;

class MappingIntegrity extends DataIntegrity
{
    public array $events = [
        AttributeItemRemoving::class => 'onAttributeItemRemoving',
        'eloquent.deleting: '.Mapping::class => 'onMappingDeleting',
        'eloquent.deleting: '.\App\Models\Mapping::class => 'onMappingDeleting',
        'eloquent.deleted: '.MarkerGroup::class => 'onMarkerGroupDeleted',
        'eloquent.deleted: '.\App\Models\MarkerGroup::class => 'onMarkerGroupDeleted',
        'eloquent.saved: '.MarkerGroup::class => 'onMarkerGroupSaved',
        'eloquent.saved: '.\App\Models\MarkerGroup::class => 'onMarkerGroupSaved',
        'eloquent.deleted: '.Category::class => 'onCategoryDeleted',
        'eloquent.deleted: '.\App\Models\Category::class => 'onCategoryDeleted',
    ];

    public function onAttributeItemRemoving(AttributeItemRemoving $event): void
    {
        $mapping = $event->model;
        if (! $mapping instanceof Mapping) {
            return;
        }
        /*
         * When removing a relationship from a mapping we also need to
         * remove all the marker groups on that relationship.
         */
        if ($event->attribute === 'relationships') {
            /** @var \App\Models\Mapping $mapping */
            $mapping->markerGroups
                ?->where('relationship.id', $event->item->id())
                ->each(function (MappingMarkerGroup $markerGroup) use ($mapping) {
                    $mapping->removeMarkerGroup($markerGroup->id());
                });
        }

    }

    public function onMappingDeleting(Mapping $mapping): void
    {
        $mapping = (new \App\Models\Mapping)->forceFill($mapping->getAttributes());
        if (! $mapping->isForceDeleting()) {
            return;
        }
        $mapping->base->mappings
            /** @phpstan-ignore-next-line This is the correct closure definition??? */
            ->each(function (Mapping $otherMapping) use ($mapping) {
                if ($otherMapping->is($mapping)) {
                    return;
                }
                $otherMapping->relationships->filter(function (Relationship $relationship) use ($mapping) {
                    return (int) $relationship->toId() === $mapping->getKey();
                })->each(function (Relationship $relationship) use ($otherMapping) {
                    $otherMapping->removeRelationship($relationship->id());
                });
            });
    }

    public function onMarkerGroupDeleted(MarkerGroup $markerGroup): void
    {
        $markerGroup = (new \App\Models\MarkerGroup)->forceFill($markerGroup->getAttributes());
        $markerGroup->base
            ->mappings
            /** @phpstan-ignore-next-line This should not be an error */
            ->each(function (\App\Models\Mapping $mapping) use ($markerGroup) {
                $mapping->removeMarkerGroup($markerGroup);
            });
    }

    public function onMarkerGroupSaved(MarkerGroup $markerGroup): void
    {
        if ($markerGroup->wasChanged('name')) {
            $originalName = $markerGroup->getOriginal('name');
            $markerGroup = (new \App\Models\MarkerGroup)->forceFill($markerGroup->getAttributes());
            $markerGroup->base
                ->mappings()
                ->eachById(function (\App\Models\Mapping $mapping) use ($markerGroup, $originalName) {
                    $marker = $mapping->markerGroups?->firstWhere('group', $markerGroup->id);
                    if ($marker && strcasecmp($marker->name, $originalName) === 0) {
                        $marker->name = $markerGroup->name;
                        $mapping->updateMarkerGroup($marker->id, [
                            ...$marker->toArray(),
                            'type' => $marker->type,
                        ]);
                    }
                });
        }
    }

    public function onCategoryDeleted(Category $category): void
    {
        $category = (new \App\Models\Category)->forceFill($category->getAttributes());
        $category->base
            ->mappings()
            ->withTrashed()
            ->eachById(function (Mapping $mapping) use ($category) {
                $filterFields = $this->removeCategoryFields($mapping->fields, $category->id);
                $mapping->fields = $filterFields;
                $mapping->save();
            });
    }

    protected function removeCategoryFields(FieldCollection $fields, int $categoryId): FieldCollection
    {
        return $fields->reject(function ($field) use ($categoryId) {
            return $field instanceof CategoryField && $field->option('category') === $categoryId;
        })->each(function ($field) use ($categoryId) {
            if ($field instanceof MultiField) {
                $field->updateOptions([
                    'fields' => $this->removeCategoryFields($field->fields(), $categoryId)->toArray(),
                ]);
            }

            return $field;
        });
    }
}
