<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Actions\Core\ActionType;
use App\Events\MappingSaved;
use App\Core\MappingActionType;
use App\Models\Contracts\Domain;
use App\Core\MappingDesignAction;
use App\Core\MappingUpdateAction;
use App\Core\MappingActionRecorder;
use App\Core\MappingActionTranslator;
use Database\Factories\MappingFactory;
use Actions\Models\Concerns\HasActions;
use App\Core\Mappings\Features\Feature;
use App\Elasticsearch\QueryUpdateEngine;
use Illuminate\Database\Eloquent\Builder;
use Actions\Core\Contracts\ActionRecorder;
use App\CustomActions\MappingCreateAction;
use Elastic\ScoutDriverPlus\Support\Query;
use Actions\Models\Contracts\ActionSubject;
use LighthouseHelpers\Concerns\HasGlobalId;
use Mappings\Models\Mapping as BaseMapping;
use Actions\Core\Contracts\ActionTranslator;
use Illuminate\Database\Eloquent\Collection;
use LighthouseHelpers\Core\ModelBatchLoader;
use LaravelUtils\Database\Eloquent\Casts\CSV;
use Actions\Models\Concerns\TranslatesActions;
use App\CustomActions\MappingFieldSavedAction;
use App\Core\Mappings\Features\FeatureCollection;
use App\Core\Mappings\Markers\MappingMarkerGroup;
use Mappings\Models\Concerns\DefinesGraphQLTypes;
use App\Core\Mappings\Features\MappingFeatureType;
use App\Models\Concerns\HasBaseScopedRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\CustomActions\MappingRelationshipSavedAction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LighthouseHelpers\Exceptions\ValidationException;
use Actions\Models\Contracts\ActionTranslatorProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mappings\Core\Mappings\Relationships\Relationship;
use App\Core\Mappings\Markers\MappingMarkerGroupCollection;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;
use Mappings\Core\Mappings\Relationships\RelationshipCollection;

/**
 * Class Mapping
 *
 * @property int $space_id
 * @property \App\Core\Mappings\Features\FeatureCollection $features
 * @property \App\Core\Mappings\Markers\MappingMarkerGroupCollection|null $markerGroups
 * @property string[]|null $templateRefs
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $pages
 */
class Mapping extends BaseMapping implements ActionSubject, ActionTranslatorProvider, Domain
{
    use AdvancedSoftDeletes;
    use ConvertsCamelCaseAttributes;
    use DefinesGraphQLTypes;
    use HasActions;
    use HasBaseScopedRelationships;
    use HasFactory;
    use HasGlobalId;
    use TranslatesActions;

    public array $deleteCascadeRelationships = [
        'items' => 'queue',
        'pages',
    ];

    protected $dispatchesEvents = [
        'saved' => MappingSaved::class,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'design' => 'object',
        'template_refs' => CSV::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_refs',
        'design',
        'features',
        'marker_groups',
    ];

    protected array $collectionAttributes = [
        'features' => FeatureCollection::class,
        'marker_groups' => MappingMarkerGroupCollection::class,
    ];

    protected static array $customActions = [
        ActionType::CREATE => MappingCreateAction::class,
        ActionType::UPDATE => MappingUpdateAction::class,
        MappingActionType::ADD_MAPPING_FIELD => MappingFieldSavedAction::class,
        MappingActionType::CHANGE_MAPPING_FIELD => MappingFieldSavedAction::class,
        MappingActionType::ADD_MAPPING_RELATIONSHIP => MappingRelationshipSavedAction::class,
        MappingActionType::CHANGE_MAPPING_DESIGN => MappingDesignAction::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Space, \App\Models\Mapping>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Page>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function featureEnabled(MappingFeatureType $type): bool
    {
        return (bool) $this->features->find($type);
    }

    public function enableFeature(MappingFeatureType $type, array $options = []): ?Feature
    {
        $feature = $this->features->find($type);
        if ($feature) {
            return $this->updateFeatureOptions($type, $options);
        }

        /** @var \App\Core\Mappings\Features\Feature|null $feature */
        $feature = $this->saveToCollection('features', ['val' => $type, 'options' => $options]);

        return $feature;
    }

    public function disableFeature(MappingFeatureType $type): ?Feature
    {
        /** @var \App\Core\Mappings\Features\Feature|null $feature */
        $feature = $this->removeFromCollection('features', $type);

        return $feature;
    }

    public function updateFeatureOptions(MappingFeatureType $type, array $options): ?Feature
    {
        /** @var \App\Core\Mappings\Features\Feature|null $feature */
        $feature = $this->changeInCollection('features', $type, $options);

        return $feature;
    }

    public function addMarkerGroup(MarkerGroup|array $options): MappingMarkerGroup
    {
        if ($options instanceof MarkerGroup) {
            $options = ['group' => $options];
        }

        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $markerGroup */
        $markerGroup = $this->saveToCollection('marker_groups', $options);

        return $markerGroup;
    }

    public function removeMarkerGroup(int|string|MarkerGroup $id): ?MappingMarkerGroup
    {
        if ($id instanceof MarkerGroup) {
            $mappingMarkerGroups = $this->markerGroups?->where('group', $id->getKey());
            $mappingMarkerGroups?->each(fn (MappingMarkerGroup $mappingMarkerGroup) => $this->removeMarkerGroup($mappingMarkerGroup->id()));

            return $mappingMarkerGroups?->first();
        }
        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $markerGroup */
        $markerGroup = $this->removeFromCollection('marker_groups', $id);

        return $markerGroup;
    }

    public function updateMarkerGroup(string $id, array $options): ?MappingMarkerGroup
    {
        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $markerGroup */
        $markerGroup = $this->changeInCollection('marker_groups', $id, $options);

        return $markerGroup;
    }

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            while ($model->isDirty('api_name') && $model->apiNameExists()) {
                $model->api_name = increment_string_suffix($model->api_name);
            }
            while ($model->isDirty('api_singular_name') && $model->apiSingularNameExists()) {
                $model->api_singular_name = increment_string_suffix($model->api_singular_name);
            }
        });

        static::saved(function (self $model) {
            if ($model->wasChanged('features') && ! $model->trashed()) {
                $id = $model->getKey();
                $features = $model->features->map->type()->map->value;
                // I would love to extract this out to a mixin that does this
                // automatically guessing the query from the relationship. But
                // that depends on how it might be used in other places, so
                // leaving it here for now.
                dispatch(function () use ($id, $features) {
                    $engine = resolve(QueryUpdateEngine::class);
                    $engine->updateByQuery(
                        (new Item)->searchableAs(),
                        Query::bool()->must(Query::term()->field('mapping_id')->value($id)),
                        ['features' => $features]
                    );
                });
            }
        });
    }

    public function apiSingularNameExists(): bool
    {
        return self::query()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->where('api_name', $this->api_singular_name)
                    ->orWhere('api_singular_name', $this->api_singular_name);
            })
            ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->id))
            ->exists();
    }

    public function apiNameExists(): bool
    {
        return self::query()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->where('api_name', $this->api_name)
                    ->orWhere('api_singular_name', $this->api_name);
            })
            ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->id))
            ->exists();
    }

    public static function getActionRecorder(): ActionRecorder
    {
        return resolve(MappingActionRecorder::class);
    }

    public static function getActionTranslator(): ActionTranslator
    {
        return resolve(MappingActionTranslator::class);
    }

    public function relationshipsWithMappings(): RelationshipCollection
    {
        $relationships = $this->relationships;
        if (request()->hasMacro('getMappingContext')) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mapping> $mappings */
            $mappings = request()->getMappingContext();
        } else {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mapping> $mappings */
            $mappings = $this::findMany($relationships->map->toId());
        }
        $mappings = $mappings->keyBy(fn (Mapping $mapping) => $mapping->getKey());

        return $relationships->filter(function (Relationship $relationship) use ($mappings) {
            $mapping = $mappings->get($relationship->toId());
            if ($mapping) {
                $relationship->to = $mapping;
            }

            return (bool) $mapping;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\MarkerGroup>
     */
    public function markerGroupModels(): Collection
    {
        $mappingGroups = $this->markerGroups;

        return MarkerGroup::query()->findMany($mappingGroups?->map->group ?: []);
    }

    public static function formatGroupActionPayload(?int $groupId): ?Deferred
    {
        return $groupId ? ModelBatchLoader::instanceFromModel(MarkerGroup::class)->loadAndResolve(
            $groupId, [],
            fn ($group): string => $group->name
        ) : null;
    }

    /**
     * @return \Database\Factories\MappingFactory|\Mappings\Database\Factories\MappingFactory
     */
    protected static function newFactory()
    {
        return MappingFactory::new();
    }

    /**
     * Check to see if a marker group includes a marker which is
     * used as a subset page filter. The method returns
     * true as soon as the first marker which
     * is used as a filter is found.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page>
     */
    protected function pagesUsingMarkersAsFilter(MarkerGroup $markerGroup): Collection
    {
        /** @var \Illuminate\Support\Collection<int,\App\Models\Marker> $markers */
        $markers = $markerGroup->markers;
        $markerIds = $markers->map(fn (Marker $marker) => $marker->global_id);

        return $this
            ->pages
            ->filter(function (Page $page) use ($markerIds) {
                $markerFilters = $page->markerFilters;

                if (\is_null($markerFilters) || ! \count($markerFilters)) {
                    return false;
                }

                $markerFilterIds = collect($markerFilters)->pluck('markerId');

                foreach ($markerFilterIds as $markerFilterId) {
                    if ($markerIds->contains($markerFilterId)) {
                        return true;
                    }
                }

                return false;
            })->values();
    }

    public function validateRemovingMarkerGroup(MarkerGroup|MappingMarkerGroup $markerGroup, string $field): void
    {
        $markerGroup = $markerGroup instanceof MarkerGroup ? $markerGroup : $markerGroup->markerGroup();
        $pagesUsingMarkersAsFilter = $this->pagesUsingMarkersAsFilter($markerGroup);
        if ($pagesUsingMarkersAsFilter->isNotEmpty()) {
            $pageNames = $pagesUsingMarkersAsFilter->implode('name', '", "');
            throw ValidationException::withMessages([
                $field => [trans_choice(
                    'validation.custom.mapping_marker_group.used',
                    $pagesUsingMarkersAsFilter->count(),
                    ['name' => $markerGroup->name, 'pages' => "\"$pageNames\""],
                )]]);
        }
    }
}
