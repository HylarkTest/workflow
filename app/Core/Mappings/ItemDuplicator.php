<?php

declare(strict_types=1);

namespace App\Core\Mappings;

use App\Models\Item;
use App\Models\Image;
use Mappings\Core\Mappings\Fields\Field;
use App\Core\Mappings\Features\MappingFeatureType;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Mappings\Core\Mappings\Fields\Types\ImageField;
use Mappings\Core\Mappings\Fields\Types\MultiField;
use App\Core\Features\Repositories\PinItemRepository;
use App\Core\Features\Repositories\LinkItemRepository;
use App\Core\Features\Repositories\NoteItemRepository;
use App\Core\Features\Repositories\TodoItemRepository;
use App\Core\Features\Repositories\EventItemRepository;
use Mappings\Core\Mappings\Fields\Types\SystemNameField;
use App\Core\Features\Repositories\DocumentItemRepository;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class ItemDuplicator
{
    protected static array $featureFields = [
        MappingFeatureType::TIMEKEEPER->value => ['start_at', 'due_by', 'completed_at'],
    ];

    protected bool $withMarkers = false;

    protected bool $withAssignees = false;

    protected bool $withRelationships = false;

    /**
     * @var \App\Core\Mappings\Features\MappingFeatureType[]
     */
    protected array $cloneFeatures = [];

    /**
     * @var \App\Core\Mappings\Features\MappingFeatureType[]
     */
    protected array $withFeatures = [];

    public function __construct(protected Item $original) {}

    public function withMarkers(): self
    {
        $this->withMarkers = true;

        return $this;
    }

    public function withAssignees(): self
    {
        $this->withAssignees = true;

        return $this;
    }

    public function withRelationships(): self
    {
        $this->withRelationships = true;

        return $this;
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType[]  $features
     */
    public function withFeatures(array $features): self
    {
        $this->withFeatures = $features;

        return $this;
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType[]  $features
     */
    public function cloneFeatures(array $features): self
    {
        $this->cloneFeatures = $features;

        return $this;
    }

    public function duplicate(): Item
    {
        $itemDuplicated = $this->original->replicate($this->getFieldsToExclude());

        $itemDuplicated->fill([
            'data' => $this->updateItemsData($itemDuplicated->data, $this->original->mapping->fields),
        ]);

        $itemDuplicated->save();

        $this->copyExtraData($itemDuplicated);

        return $itemDuplicated;
    }

    protected function getFieldsToExclude(): array
    {
        return collect(static::$featureFields)
            ->filter(fn ($fields, $feature) => ! in_array(MappingFeatureType::from($feature), $this->withFeatures, true))
            ->collapse()
            ->all();
    }

    protected function updateItemsData(array $data, FieldCollection $fields): array
    {
        return collect($data)
            ->mapWithKeys(function ($dataValue, $fieldId) use ($fields) {
                $field = $fields->find($fieldId);
                if (! $field) {
                    return [];
                }

                return [$fieldId => match (true) {
                    $field instanceof SystemNameField => $this->updateSystemName($dataValue),
                    $field instanceof ImageField => $this->updateImageField($dataValue, $field),
                    $field instanceof MultiField => $this->updateMultiField($dataValue, $field),
                    default => $dataValue,
                }];
            })
            ->all();
    }

    protected function updateMultiField(array $data, MultiField $field): array
    {
        return $this->updateListOrSingleField(
            $field,
            $data,
            fn ($data) => $this->updateItemsData($data, $field->fields())
        );
    }

    protected function updateSystemName(array $systemName): array
    {
        return [
            ...$systemName,
            '_v' => $systemName['_v'].' (Copy)',
        ];
    }

    protected function updateImageField(array $imageData, ImageField $field): array
    {
        return $this->updateListOrSingleField(
            $field,
            $imageData,
            fn ($imageData) => $this->updateImageValue($imageData)
        );
    }

    /**
     * @template T
     *
     * @param  array{ _c?: array<array{ _v?: T }> }|array{ _v?: T }  $data
     * @param  \Closure(T|null): (T|null)  $cb
     * @return array{ _c: array<array{ _v?: T }> }|array{ _v?: T }
     */
    protected function updateListOrSingleField(Field $field, $data, $cb)
    {
        if ($field->isList()) {
            /** @var array{ _c?: array<array{ _v?: T }> } $data */
            return [
                ...$data,
                '_c' => collect($data['_c'] ?? [])
                    ->map(fn ($subData) => [
                        ...$subData,
                        '_v' => $cb($subData['_v'] ?? null),
                    ])
                    ->all(),
            ];
        }

        /** @var array{ _v?: T } */
        return [
            ...$data,
            '_v' => $cb($data['_v'] ?? null),
        ];
    }

    private function updateImageValue(array $imageData): array
    {
        foreach (['image', 'originalImage'] as $key) {
            if (isset($imageData[$key])) {
                $imageData[$key] = $this->cloneImage((int) $imageData[$key]);
            }
        }

        return $imageData;
    }

    private function cloneImage(int $imageId): int
    {
        $image = Image::query()->findOrFail($imageId);

        return Image::createFromItem($image)->id;
    }

    protected function copyExtraData(Item $duplicate): void
    {
        $original = $this->original;
        if ($this->withMarkers) {
            foreach ($original->getMarkers() as $marker) {
                $duplicate->markers()->attach($marker, ['context' => $marker->pivot->context ?? null]);
            }
            $duplicate->unsetRelation('markers');
        }

        if ($this->withAssignees) {
            foreach ($this->getAssigneesGrouped($original) as $assigneeInfo) {
                $duplicate->assignees()->attach($assigneeInfo['assignees'], ['group_id' => $assigneeInfo['groupId']]);
            }
        }

        if ($this->withRelationships) {
            $original->mapping->relationships->map(function ($relationship) use ($original, $duplicate) {
                if (in_array($relationship->type, [RelationshipType::MANY_TO_ONE, RelationshipType::MANY_TO_MANY])) {
                    $original->allRelatedItems()
                        ->where('relation_id', $relationship->id)
                        ->get()
                        ->map(function ($relatedItem) use ($relationship, $duplicate) {
                            $relationship->add($duplicate, $relatedItem);
                        });
                }
            });
        }

        foreach ($this->cloneFeatures as $featureType) {
            $repository = $this->getRepositoryForFeatureType($featureType);
            $relation = $this->getRelationForFeatureType($this->original, $featureType);
            if ($repository && $relation) {
                foreach ($relation->get() as $featureItem) {
                    $repository
                        ->duplicateFeatureItem($original->base, $featureItem->getKey(), ['withMarkers' => true, 'withAssociations' => false, 'withAssignees' => true])
                        ->items()
                        ->attach($duplicate->id);
                }
            }
        }

        foreach ($this->withFeatures as $featureType) {
            $originalRelation = $this->getRelationForFeatureType($original, $featureType);
            $duplicateRelation = $this->getRelationForFeatureType($duplicate, $featureType);
            if ($originalRelation && $duplicateRelation) {
                $duplicateRelation->attach($originalRelation->pluck('id'));
            }
        }
    }

    protected function getRepositoryForFeatureType(MappingFeatureType $featureType): null|TodoItemRepository|EventItemRepository|NoteItemRepository|LinkItemRepository|PinItemRepository|DocumentItemRepository
    {
        return match ($featureType) {
            MappingFeatureType::TODOS => resolve(TodoItemRepository::class),
            MappingFeatureType::EVENTS => resolve(EventItemRepository::class),
            MappingFeatureType::NOTES => resolve(NoteItemRepository::class),
            MappingFeatureType::LINKS => resolve(LinkItemRepository::class),
            MappingFeatureType::PINBOARD => resolve(PinItemRepository::class),
            MappingFeatureType::DOCUMENTS => resolve(DocumentItemRepository::class),
            default => null,
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Todo>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Event>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Note>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Link>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Pin>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Document>|null
     */
    protected function getRelationForFeatureType(Item $item, MappingFeatureType $featureType)
    {
        return match ($featureType) {
            MappingFeatureType::TODOS => $item->todos(),
            MappingFeatureType::EVENTS => $item->events(),
            MappingFeatureType::NOTES => $item->notes(),
            MappingFeatureType::LINKS => $item->links(),
            MappingFeatureType::PINBOARD => $item->pins(),
            MappingFeatureType::DOCUMENTS => $item->documents(),
            default => null,
        };
    }

    /**
     * @return array|array[]
     */
    protected function buildArrayWithRelation(array $with): array
    {
        return collect($with)
            ->filter(fn ($value, $key) => $value && str_starts_with($key, 'with'))
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'withFeaturesTodos' => ['features.todos' => TodoItemRepository::class],
                'withFeaturesEvents' => ['features.events' => EventItemRepository::class],
                'withFeaturesNotes' => ['features.notes' => NoteItemRepository::class],
                'withFeaturesLinks' => ['features.links' => LinkItemRepository::class],
                'withFeaturesPins' => ['features.pins' => PinItemRepository::class],
                'withFeaturesDocuments' => ['features.documents' => DocumentItemRepository::class],
                default => ["records.$key" => $value]
            })
            ->undot()
            ->toArray();
    }

    protected function getAssigneesGrouped(Item $item): array
    {
        return $item->assignees()
            ->get(['group_id', 'member_id'])
            ->groupBy('group_id')
            ->map(function ($assignees, $groupId) {
                return [
                    'assignees' => $assignees->pluck('member_id')->toArray(),
                    'groupId' => $groupId,
                ];
            })
            ->values()
            ->toArray();
    }
}
