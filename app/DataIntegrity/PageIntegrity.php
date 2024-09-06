<?php

declare(strict_types=1);

namespace App\DataIntegrity;

use App\Models\Page;
use App\Models\SavedFilter;
use Illuminate\Support\Str;
use App\Core\Pages\PageType;
use Mappings\Models\Mapping;
use App\Models\PersonalPageSettings;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Core\Preferences\PersonalPagePreferences;
use Mappings\Core\Mappings\Fields\Types\MultiField;

class PageIntegrity extends DataIntegrity
{
    public array $events = [
        'eloquent.saved: '.Mapping::class => 'onMappingSaved',
        'eloquent.saved: '.\App\Models\Mapping::class => 'onMappingSaved',
        'eloquent.deleted: '.SavedFilter::class => 'onSavedFilterSaved',
    ];

    public function onMappingSaved(Mapping $mapping): void
    {
        $mapping = (new \App\Models\Mapping)->newFromBuilder($mapping->getAttributes());
        $mapping->pages
            ->each(function (Page $page) use ($mapping) {
                $page->setRelation('mapping', $mapping->withoutRelations());
                $this->validateDesign($page);
            });
    }

    public function onSavedFilterSaved(SavedFilter $filter): void
    {
        $filter->base->pages
            ->load('personalSettings')
            ->where('type', PageType::ENTITIES)
            ->each(function (Page $page) use ($filter) {
                if ($page->defaultFilterId === $filter->id) {
                    $page->defaultFilterId = null;
                    $page->save();
                }
                $page->personalSettings->each(function (PersonalPageSettings $pageSettings) use ($filter) {
                    $pageSettings->updatePreferences(function (PersonalPagePreferences $preferences) use ($filter) {
                        if ($preferences->personalDefaultFilterId === $filter->getKey()) {
                            $preferences->personalDefaultFilterId = null;
                        }
                    });
                });
            });
    }

    protected function validateDesign(Page $page): void
    {
        Page::withoutEvents(function () use ($page) {
            if (! $page->mapping) {
                return;
            }
            $fields = $page->mapping->fields;
            $fieldIds = $fields->pluck('id');
            $flatFieldIds = $fields->flatMap(function (Field $field) {
                if ($field instanceof MultiField) {
                    return [$field->id(), ...$field->fields()->map(fn (Field $subField) => $field->id().'>'.$subField->id())];
                }

                return [$field->id()];
            });
            $relationshipIds = $page->mapping->relationships->pluck('id');
            $markerGroupIds = ($page->mapping->markerGroups?->pluck('group') ?? collect())->map(fn ($id) => resolve(GlobalId::class)->encode('MarkerGroup', $id));
            $config = $page->config;
            /** @var \Illuminate\Support\Collection<int, string> $newFields */
            /** @phpstan-ignore-next-line  */
            $newFields = collect($config['newFields'] ?? []);
            $newFields = $newFields->intersect($fieldIds);
            $config['newFields'] = $newFields->values()->all();

            $design = $page->design ?: [];
            foreach ($design['views'] ?? [] as $index => $view) {
                /** @var \Illuminate\Support\Collection<int, array> $visibleData */
                /** @phpstan-ignore-next-line  */
                $visibleData = collect($view['visibleData'] ?? []);
                $visibleData = $visibleData->filter(function ($data) use ($flatFieldIds, $relationshipIds, $markerGroupIds) {
                    if (isset($data['dataType']) && $data['dataType'] === 'FIELDS') {
                        return $flatFieldIds->contains(Str::before($data['formattedId'], '.'));
                    }
                    if (isset($data['dataType']) && $data['dataType'] === 'RELATIONSHIPS') {
                        return $relationshipIds->contains($data['formattedId']);
                    }
                    if (isset($data['dataType']) && $data['dataType'] === 'MARKERS') {
                        return $markerGroupIds->contains($data['formattedId']);
                    }

                    return true;
                })->values();
                if ($visibleData->isNotEmpty()) {
                    $design['views'][$index]['visibleData'] = $visibleData->all();
                } else {
                    unset($design['views'][$index]['visibleData']);
                }
            }

            foreach ($design['itemDisplay'] ?? [] as $index => $display) {
                /** @var \Illuminate\Support\Collection<int, array> $fields */
                /** @phpstan-ignore-next-line  */
                $fields = collect($display['fields'] ?? []);
                $fields = $fields->filter(function ($data) use ($fieldIds) {
                    if (isset($data['dataType']) && $data['dataType'] === 'FIELDS') {
                        return $fieldIds->contains($data['formattedId']);
                    }

                    return true;
                })->values();
                if ($fields->isNotEmpty()) {
                    $design['itemDisplay'][$index]['fields'] = $fields->all();
                } else {
                    unset($design['itemDisplay'][$index]['fields']);
                }
            }
            $page->update([
                'config' => $config,
                'design' => $design,
            ]);
        });
    }
}
