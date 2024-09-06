<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Arr;
use App\Core\Pages\PageType;
use Finder\GloballySearchable;
use App\Core\ItemActionRecorder;
use Markers\Models\MarkableModel;
use Markers\Models\MarkablePivot;
use App\Core\ItemActionTranslator;
use Timekeeper\Core\DeadlineStatus;
use App\Models\Contracts\Assignable;
use Mappings\Models\Item as BaseItem;
use App\Models\Concerns\CanBeAssigned;
use App\Models\Concerns\CanBeImported;
use App\Models\Concerns\HasAllMarkers;
use Actions\Models\Concerns\HasActions;
use App\Models\Concerns\CanBeFavorited;
use App\Models\Concerns\CanBePreviewed;
use App\Models\Concerns\RelatedToEmails;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Actions\Core\Contracts\ActionRecorder;
use App\Core\Mappings\FieldFilterOperator;
use Actions\Models\Contracts\ActionSubject;
use App\Core\Mappings\MarkerFilterOperator;
use Actions\Core\Contracts\ActionTranslator;
use Mappings\Core\Mappings\Fields\FieldType;
use App\Models\Concerns\RelatedToOtherModels;
use App\Models\Concerns\ScoutAndFinderSearchable;
use App\Models\Concerns\HasBaseScopedRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mappings\Models\Relationships\CustomRelationship;
use Actions\Models\Contracts\ActionTranslatorProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mappings\Core\Mappings\Relationships\Relationship;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LighthouseHelpers\Contracts\MultipleGraphQLInterfaces;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

/**
 * @property string $name
 * @property \App\Models\Mapping $mapping
 * @property int $priority
 * @property \Illuminate\Support\Carbon $favorited_at
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon $due_by
 * @property \Illuminate\Support\Carbon $completed_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $allRelatedItems
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $allInverseRelatedItems
 * @property \Illuminate\Database\Eloquent\Relations\Pivot|null $pivot
 */
class Item extends BaseItem implements ActionSubject, ActionTranslatorProvider, Assignable, GloballySearchable, MarkableModel, MultipleGraphQLInterfaces
{
    use AdvancedSoftDeletes;
    use CanBeAssigned;
    use CanBeFavorited;
    use CanBeImported;
    use CanBePreviewed;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasAllMarkers {
        markers as traitMarkers;
    }
    use HasBaseScopedRelationships;
    use HasFactory;
    use RelatedToEmails;
    use RelatedToOtherModels;
    use ScoutAndFinderSearchable;

    protected $casts = [
        'favorited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'start_at' => 'datetime',
        'due_by' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $fillable = [
        'favorited_at',
        'priority',
        'name',
        'start_at',
        'due_by',
        'completed_at',
    ];

    protected array $actionIgnoredColumns = [
        'name',
        'mapping_id',
    ];

    public function getPriority(): int
    {
        return $this->priority ?? 0;
    }

    public function relatedItems(Relationship $relationship): CustomRelationship
    {
        $relationship = (new CustomRelationship($this, $relationship));
        if (tenancy()->tenant?->getKey()) {
            $relationship->wherePivot('base_id', tenancy()->tenant->getKey());
        } else {
            /** @phpstan-ignore-next-line  */
            $relationship->whereColumn($relationship->qualifyPivotColumn('base_id'), $this->qualifyColumn('base_id'));
        }

        return $relationship->using(MarkablePivot::class)
            ->withPivot('relation_id')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker>
     */
    public function markers(): MorphToMany
    {
        return $this->traitMarkers()->withPivot('context');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Item>
     */
    public function allRelatedItems(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'relationships',
            'foreign_id',
            'related_id',
        )->using(MarkablePivot::class)
            ->withPivot('relation_id')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Item>
     */
    public function allInverseRelatedItems(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'relationships',
            'related_id',
            'foreign_id',
        )->using(MarkablePivot::class)
            ->withPivot('relation_id')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapping, \Mappings\Models\Item>
     *
     * @phpstan-ignore-next-line
     */
    public function mapping(): BelongsTo
    {
        /** @phpstan-ignore-next-line  */
        return parent::mapping()
            // If the mapping is deleted, the item is as well, so we could be
            // dealing with soft deleted code.
            ->withTrashed();
    }

    /**
     * @return array{primary: string|array<int, array{text: mixed, map: string}>, secondary?: string|array<string>}
     */
    public function toGloballySearchableArray(): array
    {
        $textFields = $this->mapping->fields->filter(fn (Field $field) => $field instanceof StringableField && $field->type()->isNot(FieldType::SYSTEM_NAME()));

        /** @var string[] $secondaryTerms */
        $secondaryTerms = collect($textFields->toBase()->flatMap(function (Field $field): array {
            // TODO: Index range
            if ($field->option('isRange')) {
                return [];
            }
            $value = $this->valueDataForField($field);
            if (! $field->isList()) {
                $value = [$value];
            }

            return array_map(fn ($val) => [
                'text' => $val,
                'map' => $field->id(),
            ], $value ?? []);
        }))->merge($this->secondarySearchableArray())
            ->filter(fn (array $item): bool => (bool) $item['text'])->values()->all();

        return [
            'id' => $this->id,
            'space_id' => $this->mapping->space_id,
            'primary' => $this->mapping->fieldsOfType(FieldType::SYSTEM_NAME())
                ->toBase()->map(function (Field $field) {
                    return [
                        'text' => $this->valueDataForField($field),
                        'map' => $field->id(),
                    ];
                })->values()->all(),
            'secondary' => $secondaryTerms,
        ];
    }

    public static function esFieldTypeMap(): array
    {
        return [
            'text_fields' => [
                // FieldType::ADDRESS(),
                FieldType::EMAIL(),
                FieldType::FORMATTED(),
                FieldType::LINE(),
                FieldType::NAME(),
                FieldType::SYSTEM_NAME(),
                FieldType::PARAGRAPH(),
                FieldType::PHONE(),
                FieldType::URL(),
            ],
            'date_fields' => [
                FieldType::DATE(),
                FieldType::DATE_TIME(),
            ],
            'keyword_fields' => [
                FieldType::CATEGORY(),
                FieldType::CURRENCY(),
                FieldType::RATING(),
                FieldType::LOCATION(),
                FieldType::SELECT(),
                FieldType::MONEY(),
                FieldType::SALARY(),
                FieldType::NUMBER(),
                FieldType::PERCENTAGE(),
                FieldType::TIME(),
            ],
            'boolean_fields' => [
                FieldType::BOOLEAN(),
            ],
            'integer_fields' => [
                FieldType::DURATION(),
                FieldType::INTEGER(),
                FieldType::VOTE(),
            ],
        ];
    }

    public function toSearchableArray(): array
    {
        $map = static::esFieldTypeMap();

        $mapping = $this->mapping;

        $getFieldsForTypes = function ($types) use ($mapping) {
            /** @var \Illuminate\Support\Collection<int, array{value: mixed, field: string}> $mappedValues */
            $mappedValues = $mapping->fieldsOfType($types)->flatMap(function (Field $field) {
                if ($field->option('isRange')) {
                    return [];
                }
                $value = $this->searchableDataForField($field);

                $sortableValue = $this->sortableDataForField($field);
                if (! $field->isList()) {
                    $value = [$value];
                }

                return array_map(fn ($val) => [
                    ...($val !== null ? ['value' => $val] : []),
                    ...($sortableValue !== null ? ['sortable_value' => $sortableValue] : []),
                    'field' => $field->id(),
                ], $value ?: []);
            });

            /** @phpstan-ignore-next-line It is definitely a possible key */
            return $mappedValues->filter(fn (array $value) => filled($value['value'] ?? null) || filled($value['sortable_value'] ?? null))
                ->values()->all();
        };

        return [
            'id' => $this->id,
            'name' => $this->resolvePrimaryName(),
            'type' => $mapping->type,
            ...collect($map)->mapWithKeys(
                fn ($_, $key) => [$key => $getFieldsForTypes($map[$key])],
            ),
            'emails' => Arr::pluck($getFieldsForTypes(FieldType::EMAIL()), 'value'),
            'childRelations' => $this->allRelatedItems->map(
                fn ($item) => ['relation_id' => $item->pivot?->getAttribute('relation_id'), 'item_id' => $item->id]
            ),
            'parentRelations' => $this->allInverseRelatedItems->map(
                fn ($item) => ['relation_id' => $item->pivot?->getAttribute('relation_id'), 'item_id' => $item->id]
            ),
            'markers' => $this->markers->map(fn ($marker) => [
                'id' => $marker->id,
                'marker_group_id' => $marker->marker_group_id,
                /** @phpstan-ignore-next-line Context is added to the pivot in the relation */
                'context' => $marker->pivot->context,
            ]),
            'features' => $mapping->features->map->type()->map->value,
            'mapping_id' => $mapping->id,
            'space_id' => $mapping->space_id,
            'favorited_at' => $this->favorited_at,
            'priority' => $this->priority,
            'start_at' => $this->start_at,
            'due_by' => $this->due_by,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function searchableWith(): array
    {
        return ['markers:id,marker_group_id', 'mapping', 'allRelatedItems:id', 'allInverseRelatedItems:id'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Page>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'mapping_id', 'mapping_id');
    }

    /**
     * @param  \App\Models\Item  $model
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function resolveType(MultipleGraphQLInterfaces $model): string
    {
        return $model->mapping->graphql_type.'Item';
    }

    public static function getActionTranslator(): ActionTranslator
    {
        return resolve(ItemActionTranslator::class);
    }

    public static function getActionRecorder(): ActionRecorder
    {
        return resolve(ItemActionRecorder::class);
    }

    public function addMarker(Marker $marker): void
    {
        $this->markers()->attach($marker);
        if ($this->relationLoaded('markers')) {
            $this->unsetRelation('markers');
        }
        $this->searchable();
    }

    /**
     * Make all instances of the model searchable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function makeAllSearchable($chunk = null)
    {
        $self = new self;

        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        tenancy()->runForMultiple(null, function () use ($self, $softDelete, $chunk) {
            /** @phpstan-ignore-next-line  */
            $self->newQuery()
                ->when(true, function ($query) use ($self) {
                    $self->makeAllSearchableUsing($query);
                })
                ->when($softDelete, function ($query) {
                    $query->withTrashed();
                })
                ->orderBy($self->getKeyName())
                ->searchable($chunk);
        });
    }

    public function getDeadlineInfo(): array
    {
        return [
            'startAt' => $this->start_at,
            'dueBy' => $this->due_by,
            'isCompleted' => $this->completed_at !== null,
            'status' => DeadlineStatus::getStatus($this->start_at, $this->due_by, $this->completed_at),
        ];
    }

    public function existsInPage(Page $page): bool
    {
        if ($page->mapping_id !== $this->mapping_id) {
            return false;
        }
        if ($page->type === PageType::ENTITY) {
            return $page->entityId && $page->entityId === $this->id;
        }
        if ($page->fieldFilters) {
            foreach ($page->fieldFilters as $filter) {
                /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
                $field = $this->mapping->fields->find($filter['fieldId']);
                if (! $field) {
                    continue;
                }
                $value = $this->searchableDataForField($field);
                if ($field->isList() && $field->option('multiSelect')) {
                    $value = Arr::collapse($value ?: []);
                }
                $match = json_decode($filter['match']);
                $matches = $field->isList() || $field->option('multiSelect') ? \in_array($match, $value ?: [], true) : $value === $match;
                $matches = match (FieldFilterOperator::from($filter['operator'])) {
                    FieldFilterOperator::IS => $matches,
                    FieldFilterOperator::IS_NOT => ! $matches,
                };
                if (! $matches) {
                    return false;
                }
            }
        }
        if ($page->markerFilters) {
            foreach ($page->markerFilters as $filter) {
                $id = resolve(GlobalId::class)->decodeID($filter['markerId']);
                $matches = match (MarkerFilterOperator::from($filter['operator'])) {
                    MarkerFilterOperator::IS => $this->markers->contains('id', $id),
                    MarkerFilterOperator::IS_NOT => ! $this->markers->contains('id', $id),
                };
                if (! $matches) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function secondarySearchableArray(): array
    {
        return $this->getAssigneesMappedForFinder();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $item) {
            $item->name = $item->resolvePrimaryName() ?: '';
        });
    }
}
