<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Illuminate\Support\Str;
use App\Core\Pages\PageType;
use Finder\GloballySearchable;
use App\Models\Concerns\HasImage;
use Finder\CanBeGloballySearched;
use Illuminate\Http\UploadedFile;
use App\Core\PageActionTranslator;
use App\Models\Concerns\SavesFilters;
use GraphQL\Executor\Promise\Promise;
use Actions\Models\Concerns\HasActions;
use Illuminate\Support\Facades\Storage;
use App\Models\Contracts\SavedFilterModel;
use Actions\Models\Contracts\ActionSubject;
use Actions\Core\Contracts\ActionTranslator;
use GraphQL\Executor\Promise\PromiseAdapter;
use LighthouseHelpers\Core\ModelBatchLoader;
use LaravelUtils\Database\Eloquent\Casts\CSV;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Actions\Models\Contracts\ActionTranslatorProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LighthouseHelpers\Contracts\MultipleGraphQLInterfaces;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

/**
 * Class Page
 *
 * @property int $id
 * @property string[]|null $template_refs
 * @property int $space_id
 * @property int|null $mapping_id
 * @property string $description
 * @property string $path
 * @property string $symbol
 * @property array $design
 * @property array $config
 * @property string|null $image
 * @property string|null $imageUrl
 * @property \App\Core\Pages\PageType $type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Accessors
 * @property string $folder
 * @property int|null $defaultFilterId
 * @property int|null $entityId
 * @property string $name
 * @property string $singularName
 * @property array $fieldFilters
 * @property array|null $markerFilters
 * @property string[] $newFields
 * @property string[] $newMarkers
 * @property array{
 *     fields: \Closure(): \Illuminate\Support\Collection<int, string>,
 *     markers: \Closure(): \Illuminate\Support\Collection<int, string>
 * } $newData
 * @property string[] $lists
 *
 * Relationships
 * @property-read \App\Models\Mapping|null $mapping
 * @property-read \App\Models\Space $space
 * @property-read \Illuminate\Database\Eloquent\Model $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalPageSettings> $personalSettings
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\SavedFilter> $savedFilters
 */
class Page extends Model implements ActionSubject, ActionTranslatorProvider, GloballySearchable, MultipleGraphQLInterfaces, SavedFilterModel
{
    use AdvancedSoftDeletes;
    use CanBeGloballySearched;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasFactory;
    use HasImage{
        HasImage::updateImage as updateImageTrait;
    }
    use SavesFilters {
        SavesFilters::isPrivateAction as savesFiltersPrivateAction;
    }

    protected $fillable = [
        'space_id',
        'template_refs',
        'templateRefs',
        'name',
        'folder',
        'path',
        'type',
        'symbol',
        'design',
        'config',
        'singular_name',
        'singularName',
        'list_type',
        'listType',
        'lists',
        'field_filters',
        'fieldFilters',
        'marker_filters',
        'markerFilters',
        'new_fields',
        'newFields',
        'new_markers',
        'newMarkers',
        'new_data',
        'newData',
        'description',
        'entity_id',
        'entityId',
        'mapping_id',
        'image',
        'default_filter_id',
        'defaultFilterId',
    ];

    protected $casts = [
        'type' => PageType::class,
        'design' => 'array',
        'config' => 'array',
        'template_refs' => CSV::class,
    ];

    protected $with = ['mapping'];

    protected array $actionIgnoredColumns = [
        'space_id',
        'mapping_id',
        'type',
    ];

    /*
     * Silenced from the saved filter model
     */
    protected array $actionSilentFields = [
        'filters',
        'order_by',
        'group',
        'base_user_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function folder(): Attribute
    {
        return new Attribute(
            get: function (): string {
                if (str_contains($this->path ?? '', '/')) {
                    $folder = Str::beforeLast($this->path ?? '', '/');
                    if ($folder) {
                        $folder .= '/';
                    }

                    return $folder;
                }

                return '';
            },
            set: function (?string $folder): array {
                $path = $this->path ?? '';

                $path = Str::afterLast($path, '/');

                if ($folder) {
                    $folder = Str::finish($folder, '/');
                }

                return ['path' => $folder.$path];
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function name(): Attribute
    {
        return new Attribute(
            get: fn () => Str::afterLast($this->path, '/'),
            set: function (string $name): array {
                if (! str_contains($name, '/')) {
                    $name = $this->folder.$name;
                }

                return ['path' => $name];
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function singularName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->config['singularName'] ?? null,
            set: fn ($value) => $this->setConfigValue('singularName', $value),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<int, int>
     */
    public function entityId(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->config['entityId']) ? (int) $this->config['entityId'] : null,
            set: fn ($value) => $this->setConfigValue('entityId', $value),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<int, int>
     */
    public function defaultFilterId(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->config['defaultFilterId']) ? (int) $this->config['defaultFilterId'] : null,
            set: fn ($value) => $this->setConfigValue('defaultFilterId', $value),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>, string[]>
     */
    public function lists(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->config['lists'] ?? null,
            set: function ($value) {
                return $this->setConfigValue('lists', array_map(
                    fn ($list) => ($list instanceof BaseModel) ? $list->getKey() : $list,
                    $value
                ));
            },
        )->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, array>
     */
    public function fieldFilters(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->config['fieldFilters'] ?? [],
            set: fn (?array $value) => $this->setConfigValue('fieldFilters', $value ? array_map(fn ($filter) => [
                ...$filter,
                'match' => json_encode($filter['match']),
            ], $value) : $value),
        )->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, array>
     */
    public function markerFilters(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->config['markerFilters'] ?? [],
            set: fn (?array $value) => $this->setConfigValue('markerFilters', $value)
        )->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, string>
     */
    public function newFields(): Attribute
    {
        return Attribute::make(
            get: function () {
                /** @var \Illuminate\Support\Collection<int, string> $newFields */
                /** @phpstan-ignore-next-line  */
                $newFields = collect($this->config['newFields'] ?? []);
                $fields = $this->mapping?->fields->pluck('id');

                return $newFields
                    ->filter(fn (string $id) => $fields?->contains($id) ?? false);
            },
            set: fn (array $value) => $this->setConfigValue('newFields', $value)
        )->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, array>
     */
    public function newMarkers(): Attribute
    {
        return Attribute::make(
            get: function () {
                /** @var \Illuminate\Support\Collection<int, string> $newFields */
                /** @phpstan-ignore-next-line  */
                $newMarkers = collect($this->config['newMarkers'] ?? []);
                $mappingMarkers = $this->mapping?->markerGroups?->pluck('id');

                return $newMarkers
                    ->filter(fn (string $id) => $mappingMarkers?->contains($id) ?? false);
            },
            set: fn (array $value) => $this->setConfigValue('newMarkers', $value)
        )->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array, array>
     */
    public function newData(): Attribute
    {
        return new Attribute(
            get: function () {
                return [
                    'fields' => fn () => $this->newFields,
                    'markers' => fn () => $this->newMarkers,
                ];
            },
            set: function (?array $value) {
                $config = $this->config;
                if (isset($value['fields'])) {
                    $config['newFields'] = $value['fields'];
                }
                if (isset($value['markers'])) {
                    $config['newMarkers'] = $value['markers'];
                }

                return ['config' => json_encode($config, \JSON_THROW_ON_ERROR)];
            },
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, never>
     */
    public function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $image = $this->image;
            if (! $image) {
                return null;
            }
            if (filter_var($image, \FILTER_VALIDATE_URL)) {
                return $image;
            }

            return Storage::disk('images')->url($image);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapping, \App\Models\Page>
     */
    public function mapping(): BelongsTo
    {
        return $this->belongsTo(Mapping::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Space, \App\Models\Page>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Image, \App\Models\Page>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PersonalPageSettings>
     */
    public function personalSettings(): HasMany
    {
        return $this->hasMany(PersonalPageSettings::class);
    }

    public function getPersonalSettings(BaseUserPivot $member): PersonalPageSettings
    {
        $default = new PersonalPageSettings;
        $default->page()->associate($this);
        $default->baseUser()->associate($member);
        if ($this->relationLoaded('personalSettings')
            && (
                $this->personalSettings->isEmpty()
                || $this->personalSettings->contains('base_user_id', $member->getKey())
            )
        ) {
            return $this->personalSettings->firstWhere('base_user_id', $member->getKey()) ?? $default;
        }

        return $this->personalSettings()
            ->forMember($member)
            ->firstOr(fn () => $default);
    }

    /**
     * @param  \App\Models\Page  $model
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function resolveType(self|MultipleGraphQLInterfaces $model): string
    {
        return match ($model->type) {
            PageType::ENTITY => 'EntityPage',
            PageType::ENTITIES => 'EntitiesPage',
            default => 'ListPage',
        };
    }

    /**
     * @return array{primary: string|array<string>, secondary?: string|array<string>}
     */
    public function toGloballySearchableArray(): array
    {
        return [
            'id' => $this->id,
            'space_id' => $this->space_id,
            'primary' => [
                'text' => $this->name,
                'map' => 'name',
            ],
            'secondary' => [
                'text' => $this->description,
                'map' => 'description',
            ],
        ];
    }

    public function isEntitiesPage(): bool
    {
        return $this->type === PageType::ENTITIES;
    }

    public function isUnfilteredEntitiesPage(): bool
    {
        return $this->isEntitiesPage() && ! $this->hasFilters();
    }

    public function isSubsetEntitiesPage(): bool
    {
        return $this->isEntitiesPage() && $this->hasFilters();
    }

    public function hasFilters(): bool
    {
        return $this->fieldFilters || $this->markerFilters;
    }

    public function formatDesignActionPayload(): null
    {
        return null;
    }

    public static function getActionTranslator(): ActionTranslator
    {
        return resolve(PageActionTranslator::class);
    }

    public static function formatEntityIdActionPayload(int|string|null $entityId): ?Deferred
    {
        return $entityId
            ? ModelBatchLoader::instanceFromModel(Item::class)
                ->loadAndResolve((int) $entityId, [], fn (?Item $entity) => $entity?->name ?? trans('common.(unknown)'))
            : null;
    }

    public static function formatListsActionPayload(?array $lists): ?Promise
    {
        if ($lists === null) {
            return null;
        }

        $adapter = resolve(PromiseAdapter::class);

        return $adapter->all(array_map(function ($listId) use ($adapter) {
            return new Promise(ModelBatchLoader::instanceFromGlobalId($listId, fn ($list) => $list?->name), $adapter);
            /** @var string[] $name */
        }, $lists))->then(fn (array $names) => collect($names)->filter()->implode(', '));
    }

    public static function formatMarkerFiltersActionPayload(?array $markerFilters): ?Promise
    {
        if ($markerFilters === null) {
            return null;
        }

        $adapter = resolve(PromiseAdapter::class);

        return $adapter->all(array_map(function ($markerFilter) use ($adapter) {
            return new Promise(ModelBatchLoader::instanceFromGlobalId($markerFilter['markerId'], function ($marker) use ($markerFilter) {
                $name = $marker->name ?? trans('common.unknown');

                return trans("actions::description.page.change.markerFilters.{$markerFilter['operator']}", ['name' => $name]);
            }), $adapter);
        }, $markerFilters))->then(fn ($filters) => implode(', ', $filters));
    }

    /**
     * @throws \Exception
     */
    public static function formatFieldFiltersActionPayload(?array $fieldFilters, Action $action): ?SyncPromise
    {
        if ($fieldFilters === null) {
            return null;
        }

        return $fieldFilters ? $action->deferredSubject()
            ?->then(function (?self $page) use ($fieldFilters) {
                return $page?->mapping_id ? ModelBatchLoader::instanceFromModel(Mapping::class)
                    ->loadAndResolve($page->mapping_id, [], function (Mapping $mapping) use ($fieldFilters) {
                        return implode(', ', array_map(function ($fieldFilter) use ($mapping) {
                            $fieldName = $mapping->fields->firstWhere('id', $fieldFilter['fieldId'])->name ?? trans('common.unknown');

                            return trans("actions::description.pages.change.fieldFilters.{$fieldFilter['operator']}", ['name' => $fieldName, 'value' => $fieldFilter['match']]);
                        }, $fieldFilters));
                    }) : null;
            }) : null;
    }

    /**
     * @throws \Exception
     */
    public static function formatNewFieldsActionPayload(?array $newFields, Action $action): ?SyncPromise
    {
        if ($newFields === null) {
            return null;
        }

        return $newFields ? $action->deferredSubject()
            ?->then(function (?self $page) use ($newFields) {
                return $page?->mapping_id ? ModelBatchLoader::instanceFromModel(Mapping::class)
                    ->loadAndResolve($page->mapping_id, [], function (Mapping $mapping) use ($newFields) {
                        return implode(', ', array_map(fn ($newField) => $mapping->fields->firstWhere('id', $newField)->name ?? trans('common.(unknown)'), $newFields));
                    }) : null;
            }) : null;
    }

    public static function formatNewMarkersActionPayload(?array $newMarkers, Action $action): ?SyncPromise
    {
        if ($newMarkers === null) {
            return null;
        }

        return $newMarkers ? $action->deferredSubject()
            ?->then(function (?self $page) use ($newMarkers) {
                return $page?->mapping_id ? ModelBatchLoader::instanceFromModel(Mapping::class)
                    ->loadAndResolve($page->mapping_id, [], function (Mapping $mapping) use ($newMarkers) {
                        return collect($newMarkers)
                            ->map(fn ($id) => $mapping->markerGroups?->firstWhere('id', $id))
                            ->map(fn (?MappingMarkerGroup $markerGroup) => $markerGroup->name ?? trans('common.(unknown)'))
                            ->implode(', ');
                    }) : null;
            }) : null;
    }

    public static function formatDefaultFilterIdActionPayload(string|int|null $defaultFilterId): ?Deferred
    {
        if ($defaultFilterId === null) {
            return null;
        }

        return ModelBatchLoader::instanceFromModel(SavedFilter::class)
            ->loadAndResolve($defaultFilterId, [], fn ($filter) => $filter?->name ?: trans('common.unknown'));
    }

    public static function formatPersonalDefaultFilterIdActionPayload(string|int|null $defaultFilterId): ?Deferred
    {
        return static::formatDefaultFilterIdActionPayload($defaultFilterId);
    }

    public static function formatItemDisplayActionPayload(): null
    {
        return null;
    }

    protected function setConfigValue(string $key, mixed $value): array
    {
        return ['config' => json_encode(array_merge(
            $this->config ?? [],
            [$key => $value],
        ), \JSON_THROW_ON_ERROR)];
    }

    public function canSaveFilters(): bool
    {
        return $this->type !== PageType::ENTITY;
    }

    public function isPrivateAction(Action $action): bool
    {
        return $action->triggeringModel instanceof PersonalPageSettings
            || $this->savesFiltersPrivateAction($action);
    }

    public function updateImage(?UploadedFile $image): void
    {
        $this->updateImageTrait($image, 'image', 'page-images');
    }
}
