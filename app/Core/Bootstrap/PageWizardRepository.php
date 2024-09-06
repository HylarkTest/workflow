<?php

declare(strict_types=1);

namespace App\Core\Bootstrap;

use App\Models\Base;
use App\Models\Page;
use App\Models\Space;
use App\Models\Marker;
use App\Models\Mapping;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use App\Core\Pages\PageType;
use Markers\Core\MarkerType;
use Actions\Core\ActionRecorder;
use Intervention\Image\ImageManager;
use PHPStan\ShouldNotHappenException;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Database\Eloquent\Collection;
use Mappings\Core\Mappings\Fields\FieldType;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use App\Core\Mappings\Features\MappingFeatureType;
use Mappings\Core\Mappings\Relationships\RelationshipType;

/**
 * @phpstan-type MarkerGroupData array{
 *     id?: string,
 *     name: string,
 *     description?: string,
 *     templateRefs?: array<string>,
 *     type: 'TAG'|'STATUS'|'PIPELINE',
 *     markers?: array<string, array{
 *         id?: string,
 *         name: string,
 *         color?: string,
 *         order?: int,
 *     }>,
 * }
 * @phpstan-type CategoryData array{
 *     id?: string,
 *     name: string,
 *     description?: string,
 *     templateRefs?: array<string>,
 *     items?: array<int, array{
 *         name: string,
 *     }>,
 * }
 * @phpstan-type ListData array{
 *    id?: string,
 *    color?: string,
 *    name: string,
 * }
 * @phpstan-type MappingData array{
 *     id?: string,
 *     singularName?: string,
 *     name: string,
 *     type: 'PERSON'|'ITEM',
 *     templateRef?: string[],
 *     fields?: array<int, array{
 *         id?: string,
 *         type: string,
 *         options?: array,
 *     }>,
 *     features?: array<int, array>,
 *     markerGroups?: array<int, array>,
 *     relationships?: array<int, array{
 *         to: string,
 *         name?: string,
 *         apiName?: string,
 *         inverseName?: string,
 *         inverseApiName?: string,
 *     }>,
 *     examples?: array<int, array>
 * }
 * @phpstan-type PageData array{
 *     id: string,
 *     templateRefs?: string[],
 *     views?: array<int, array{
 *         visibleData: array<int, array{
 *             dataType: 'MARKERS'|'FIELDS'|'RELATIONSHIPS',
 *             formattedId: string,
 *         }>
 *     }>,
 *     pageName?: string,
 *     name: string,
 *     description?: string,
 *     folder?: string,
 *     symbol?: string,
 *     pageType?: 'ENTITY'|'ENTITIES'|'TODOS'|'LINKS'|'CALENDAR'|'DOCUMENTS'|'PINBOARD'|'NOTES',
 *     singularName?: string,
 *     newFields?: string[],
 *     defaultView?: string,
 *     lists?: string[],
 *     subset?: array{
 *         mainId: string,
 *         filter?: array{
 *             type: 'MARKER'|'FIELD',
 *             comparator: 'IS'|'IS_NOT',
 *             id: string,
 *             val: string,
 *         }
 *     }
 * }
 */
class PageWizardRepository
{
    /**
     * @var array<string, \App\Models\MarkerGroup>
     */
    protected array $markerGroupMap = [];

    /**
     * @var array<string, array<string, \App\Models\Marker>>
     */
    protected array $markerMap = [];

    /**
     * @var array<string, int>
     */
    protected array $categoryMap = [];

    /**
     * @var array<string, array<string, \App\Models\Contracts\FeatureList>>
     *
     * @phpstan-ignore-next-line This has all types of lists.
     */
    protected array $listMap = [];

    /**
     * @var array<string, \App\Models\Page>
     */
    protected array $pageMap = [];

    /**
     * @var array<string, \App\Models\Mapping>
     */
    protected array $mappingMap = [];

    protected array $designMap = [];

    public function __construct(protected Base $base)
    {
        $markerGroups = $base->markerGroups->loadMissing('markers');
        /** @phpstan-ignore-next-line keyBy makes the array the correct type, maybe this will be updated by larastan */
        $this->markerGroupMap = $markerGroups->keyBy('global_id')->all();
        /** @phpstan-ignore-next-line keyBy makes the array the correct type, maybe this will be updated by larastan */
        $this->markerMap = $markerGroups->mapWithKeys(function (MarkerGroup $group) {
            return [$group->global_id => $group->markers->keyBy('global_id')->all()];
        })->all();
        $this->categoryMap = $base->categories->pluck('id', 'global_id')->all();
        /** @phpstan-ignore-next-line keyBy makes the array the correct type, maybe this will be updated by larastan */
        $this->mappingMap = $base->mappings->keyBy('global_id')->all();
    }

    /**
     * @param  array<string, MarkerGroupData>  $markerGroups
     */
    public function buildMarkerGroups(array $markerGroups): void
    {
        foreach ($markerGroups as $data) {
            $createData = Arr::only($data, ['name', 'description', 'type']);
            $createData['template_refs'] = $data['templateRefs'] ?? null;
            /** @var \App\Models\MarkerGroup $group */
            $group = $this->base->markerGroups()->create($createData);
            if ($data['id'] ?? false) {
                $this->markerGroupMap[$data['id']] = $group;
            }

            if ($data['markers'] ?? false) {
                foreach ($data['markers'] as $markerData) {
                    /** @var \App\Models\Marker $marker */
                    $marker = $group->markers()->create(
                        Arr::only($markerData, ['name', 'color', 'order'])
                    );
                    if (($data['id'] ?? null) && ($markerData['id'] ?? null)) {
                        $this->markerMap[$data['id']][$markerData['id']] = $marker;
                    }
                }
            }
        }
    }

    /**
     * @param  array<string, CategoryData>  $categories
     */
    public function buildCategories(array $categories): void
    {
        foreach ($categories as $data) {
            $createData = Arr::only($data, ['name', 'description']);
            $createData['template_refs'] = $data['templateRefs'] ?? null;
            /** @var \Mappings\Models\Category $category */
            $category = $this->base->categories()->create($createData);
            if ($data['id'] ?? false) {
                $this->categoryMap[$data['id']] = $category->getKey();
            }

            if ($data['items'] ?? false) {
                $category->items()->createMany(
                    array_map(fn ($itemData) => Arr::only($itemData, ['name']), $data['items'])
                );
            }
        }
    }

    public function reuseMarkerGroups(array $markerGroups): void
    {
        foreach ($markerGroups as $tempId => $markerGroupId) {
            $this->markerGroupMap[$tempId] = $this->markerGroupMap[$markerGroupId];
        }
    }

    public function reuseCategories(array $categories): void
    {
        foreach ($categories as $tempId => $categoryId) {
            $this->categoryMap[$tempId] = $this->categoryMap[$categoryId];
        }
    }

    public function reuseBlueprints(array $blueprints): void
    {
        foreach ($blueprints as $tempId => $mappingId) {
            $this->mappingMap[$tempId] = $this->mappingMap[$mappingId];
        }
    }

    /**
     * @param  array<string, array<int, ListData>>  $lists
     */
    public function buildLists(array $lists, Space $space): void
    {
        foreach ($lists as $type => $listsArray) {
            $typeEnum = MappingFeatureType::from(mb_strtoupper($type));
            $relation = match ($typeEnum) {
                MappingFeatureType::TODOS => $space->todoLists(),
                MappingFeatureType::DOCUMENTS => $space->drives(),
                MappingFeatureType::EVENTS, MappingFeatureType::CALENDAR => $space->calendars(),
                MappingFeatureType::PINBOARD => $space->pinboards(),
                MappingFeatureType::LINKS => $space->linkLists(),
                MappingFeatureType::NOTES => $space->notebooks(),
                default => throw new ShouldNotHappenException('Tried to create a list for '.$type),
            };

            foreach ($listsArray as $listData) {
                $list = $relation->create(Arr::only($listData, ['color', 'name', 'templateRefs']));
                if (isset($listData['id'])) {
                    $this->listMap[$typeEnum->value][$listData['id']] = $list;
                }
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page>
     */
    public function buildPages(array $pages, Space $space): Collection
    {
        $result = (new Page)->newCollection();
        foreach ($pages as $pageData) {
            $result->push($this->buildPage($pageData, $space));
        }

        return $result;
    }

    /**
     * @param  PageData  $pageData
     */
    public function buildPage(array $pageData, Space $space): Page
    {
        $views = $pageData['views'] ?? [];

        foreach ($views as $index => $view) {
            foreach ($view['visibleData'] as $dataIndex => $data) {
                if ($data['dataType'] === 'MARKERS') {
                    $data['formattedId'] = $this->markerGroupMap[$data['formattedId']]->global_id;
                    $pageData['views'][$index]['visibleData'][$dataIndex] = $data;
                }
            }
        }

        $pageName = $pageData['pageName'] ?? $pageData['name'];
        $pageFields = [
            'name' => $pageName,
            'description' => $pageData['description'] ?? null,
            'template_refs' => $pageData['templateRefs'] ?? [],
            'folder' => $pageData['folder'] ?? '',
            'symbol' => $pageData['symbol'] ?? '',
            'type' => $pageData['pageType'] ?? null,
            'singularName' => $pageData['singularName'] ?? null,
            'newFields' => $pageData['newFields'] ?? [],
            'design' => array_filter([
                'views' => $pageData['views'] ?? [],
                'defaultView' => $pageData['defaultView'] ?? null,
            ]),
        ];

        if ($pageData['subset'] ?? null) {
            if ($pageData['subset']['filter'] ?? null) {
                if ($pageData['subset']['filter']['type'] === 'MARKER') {
                    $pageFields['markerFilters'] = [[
                        'operator' => $pageData['subset']['filter']['comparator'],
                        'markerId' => data_get($this->markerMap, $pageData['subset']['filter']['id'].'.global_id'),
                    ]];
                } elseif ($pageData['subset']['filter']['type'] === 'FIELD') {
                    $pageFields['fieldFilters'] = [[
                        'operator' => $pageData['subset']['filter']['comparator'],
                        'fieldId' => $pageData['subset']['filter']['id'],
                        'match' => $pageData['subset']['filter']['val'],
                    ]];
                }
            }
            $pageFields['type'] = PageType::ENTITIES->value;
            if (! $pageFields['design']) {
                $pageFields['design'] = $this->designMap[$pageData['subset']['mainId']] ?? null;
            }
        }

        /** @var \App\Models\Page $page */
        $page = $space->pages()->make($pageFields);
        if ($page->type === PageType::ENTITIES || $page->type === PageType::ENTITY) {
            if (isset($pageData['subset']['mainId'], $this->pageMap[$pageData['subset']['mainId']])) {
                $mapping = $this->mappingMap[$pageData['subset']['mainId']];
            } elseif (isset($this->mappingMap[$pageData['id']])) {
                $mapping = $this->mappingMap[$pageData['id']];
            } else {
                if (isset($pageFields['design'])) {
                    $this->designMap[$pageData['id']] = $pageFields['design'];
                }
            }

            if ($mapping ?? null) {
                $page->mapping()->associate($mapping);
            }
        } elseif (\in_array($page->type, PageType::listTypes(), true)) {
            $listIds = [];
            foreach ($pageData['lists'] ?? [] as $list) {
                /** @phpstan-ignore-next-line */
                $listIds[] = $this->listMap[$page->type->value][$list]->global_id;
            }
            $page->lists = $listIds;
        }
        $page->save();
        $this->pageMap[$pageData['id']] = $page;

        return $page;
    }

    public function buildMappings(array $mappingsData, Space $space): void
    {
        foreach ($mappingsData as $mappingData) {
            $this->buildMapping($mappingData, $space);
        }

        foreach ($mappingsData as $mappingData) {
            if ($mappingData['relationships'] ?? false) {
                foreach ($mappingData['relationships'] as $relationshipInfo) {
                    if (isset($this->mappingMap[$relationshipInfo['to']])) {
                        $fromMapping = $this->mappingMap[$mappingData['id']];
                        $this->addRelationshipToMapping($this->mappingMap[$relationshipInfo['to']], $relationshipInfo, $fromMapping);
                    }
                }
            }
        }
    }

    /**
     * @param  MappingData  $mappingData
     */
    public function buildMapping(array $mappingData, Space $space): void
    {
        if (isset($this->mappingMap[$mappingData['id'] ?? null])) {
            return;
        }

        if ($mappingData['fields'] ?? null) {
            $mappingData['fields'] = $this->updateCategoryFields($mappingData['fields']);
        }

        $data = Arr::only($mappingData, ['templateRefs', 'name', 'type', 'singularName', 'fields']);
        /** @phpstan-ignore-next-line */
        $fields = collect($data['fields']);
        if (! $fields->contains('type', 'SYSTEM_NAME')) {
            /** @var int|false $nameFieldIndex */
            $nameFieldIndex = $fields->search(fn ($field) => $field['type'] === 'NAME');
            if ($nameFieldIndex !== false) {
                $nameField = $fields[$nameFieldIndex];
                $nameField['type'] = 'SYSTEM_NAME';
                $fields->splice($nameFieldIndex, 1);
                $data['fields'] = [
                    $nameField,
                    ...$fields->all(),
                ];
            } else {
                $data['fields'] = [
                    ['type' => 'SYSTEM_NAME', 'name' => 'Name'],
                    ...$data['fields'],
                ];
            }
        }
        /** @phpstan-ignore-next-line  */
        $data['fields'] = collect($data['fields'])->map(function (array $field) {
            $field['id'] = $field['id'] ?? $field['nameKey'] ?? $field['type'];

            return $field;
        })->all();
        /** @var \App\Models\Mapping $mapping */
        $mapping = $space->mappings()->save(
            $this->base->mappings()->make($data)
        );

        ActionRecorder::withoutRecording(function () use ($mapping, $mappingData) {
            if ($mappingData['features'] ?? false) {
                foreach ($mappingData['features'] as $featureInfo) {
                    $this->enableFeatureInMapping($mapping, $featureInfo);
                }
            }

            if ($mappingData['markerGroups'] ?? false) {
                $this->addMarkerGroupsToMapping($mapping, $mappingData['markerGroups']);
            }
        });

        $fields = $mapping->fields;

        if (isset($mappingData['examples'])) {
            foreach ($mappingData['examples'] as $itemInfo) {
                $data = $itemInfo['data'];
                $itemInfo['data'] = $fields->mapWithKeys(static function (Field $field) use ($data) {
                    if (\array_key_exists($field->id, $data)) {
                        $fieldData = $data[$field->id];
                        if ($field->type()->is(FieldType::IMAGE()) && \is_string($fieldData['fieldValue'] ?? null)) {
                            $path = public_path($fieldData['fieldValue']);
                            $image = (new ImageManager(['driver' => 'imagick']))->make($path);
                            $fieldData = [
                                ...$fieldData,
                                'fieldValue' => [
                                    'url' => $path,
                                    'width' => $image->width(),
                                    'height' => $image->height(),
                                    'xOffset' => 0,
                                    'yOffset' => 0,
                                ],
                            ];
                        }

                        return [$field->id() => $field->serializeValue($fieldData)];
                    }

                    return [$field->id() => null];
                })->filter(fn ($data) => filled($data))->all();

                /** @var \App\Models\Item $item */
                $item = $mapping->items()->create($itemInfo);

                $markers = $itemInfo['markers'] ?? [];

                foreach ($markers as $groupId => $groupMarkers) {
                    $group = $this->markerGroupMap[$groupId];
                    /** @var \App\Core\Mappings\Markers\MappingMarkerGroupCollection $mappingMarkerGroups */
                    $mappingMarkerGroups = $mapping->markerGroups;
                    /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $mappingMarkerGroup */
                    $mappingMarkerGroup = $mappingMarkerGroups->first(
                        fn (MappingMarkerGroup $mappingGroup) => $mappingGroup->group === $group->id
                    );
                    if ($group->type === MarkerType::STATUS) {
                        $item->markers()->attach(
                            $this->getExampleMarker($groupId, $groupMarkers),
                            ['context' => $mappingMarkerGroup->id()]
                        );
                    } else {
                        foreach ($groupMarkers as $marker) {
                            $item->markers()->attach(
                                $this->getExampleMarker($groupId, $marker),
                                ['context' => $mappingMarkerGroup->id()]
                            );
                        }
                    }
                }
            }
        }
        if (isset($mappingData['id'])) {
            $this->mappingMap[$mappingData['id']] = $mapping;
        }
    }

    protected function getExampleMarker(string $groupId, string $tempMarkerId): Marker
    {
        if (isset($this->markerMap[$groupId][$tempMarkerId])) {
            return $this->markerMap[$groupId][$tempMarkerId];
        }

        $group = $this->markerGroupMap[$groupId];

        // There is some issue with the collection `random` method and PHPStan
        return Arr::random($group->markers->all());
    }

    protected function updateCategoryFields(array $fields): array
    {
        foreach ($fields as &$field) {
            if (FieldType::CATEGORY()->is($field['type'])) {
                $field['options']['category'] = $this->categoryMap[$field['options']['category']];
            } elseif (FieldType::MULTI()->is($field['type'])) {
                $field['options']['fields'] = $this->updateCategoryFields($field['options']['fields']);
            }
        }

        return $fields;
    }

    protected function addMarkerGroupsToMapping(Mapping $mapping, array $markerGroups): void
    {
        foreach ($markerGroups as $markerInfo) {
            $group = $this->markerGroupMap[$markerInfo];

            if (! $mapping->markerGroups?->contains('group', $group->getKey())) {
                $mapping->addMarkerGroup($group);
            }
        }
    }

    protected function enableFeatureInMapping(Mapping $mapping, array $featureInfo): void
    {
        $type = MappingFeatureType::from($featureInfo['val']);
        /** @var \App\Core\Mappings\Features\Feature|null $existingFeature */
        $existingFeature = $mapping->features->find($type);
        $existingFeatureOptions = $existingFeature && $existingFeature->options ? $existingFeature->toArray()['options'] : [];
        $mapping->enableFeature($type, array_merge_recursive($featureInfo['options'] ?? [], $existingFeatureOptions));
    }

    protected function addRelationshipToMapping(Mapping $to, mixed $relationshipInfo, Mapping $mapping): void
    {
        $type = RelationshipType::from($relationshipInfo['type']);
        $relationship = $mapping->addRelationship([
            'type' => $type,
            'to' => $to,
            'name' => $relationshipInfo['name'] ?? null,
            'apiName' => $relationshipInfo['apiName'] ?? null,
            'inverse' => false,
        ]);

        if ($relationship) {
            $to->addRelationship([
                'id' => $relationship->id(),
                'type' => $type->inverse(),
                'to' => $mapping,
                'name' => $relationshipInfo['inverse'] ?? null,
                'apiName' => $relationshipInfo['inverseApiName'] ?? null,
                'inverse' => true,
            ]);
        }
    }
}
