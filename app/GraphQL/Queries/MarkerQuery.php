<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Pin;
use App\Models\Item;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Event;
use GraphQL\Deferred;
use App\Models\Document;
use App\GraphQL\AppContext;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use Markers\Core\MarkerType;
use Finder\GloballySearchable;
use Markers\Events\MarkerAdded;
use Markers\Events\MarkerRemoved;
use Markers\Models\MarkableModel;
use App\Models\Concerns\Searchable;
use LighthouseHelpers\Core\Mutation;
use App\Models\Contracts\FeatureList;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Database\Query\Builder;
use Markers\Models\Collections\MarkerCollection;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\BroadcastsChanges;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Execution\ModelsLoader\ModelsLoader;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @template TList of FeatureList
 * @template TItem of FeatureListItem
 */
class MarkerQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\BroadcastsChanges<TList, TItem>
     */
    use BroadcastsChanges;

    public function index(Model $root, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        if (! isset($args['markerGroup'])) {
            throw new \InvalidArgumentException('The group id must be specified when using the MarkerQuery');
        }

        $group = $args['markerGroup'];
        $type = $group->type->value;
        $isPivot = $group->relationship;

        $relationName = $isPivot ? 'pivotMarker' : 'marker';
        $relationName = $type === 'STATUS' ? $relationName : $relationName.'s';
        $relationName .= 'FromGroup|'.$group->group;

        /** @var array<int|string> $path */
        $path = $resolveInfo->path;

        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($path, function () use ($relationName, $group) {
            return new RelationBatchLoader(new SimpleModelsLoader(
                $relationName, fn (Builder $query) => $query->where('context', $group->id())
            ));
        });

        return $instance->load($root);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name', 'color']);

        $base = $context->base();

        /** @var \App\Models\MarkerGroup $markerGroup */
        $markerGroup = $base->markerGroups()->findOrFail($args['input']['groupId']);

        if ($markerGroup->markers->count() >= 30) {
            $this->throwValidationException('input', trans('validation.exceeded'));
        }

        $marker = $markerGroup->markers()->create($data);

        $this->broadcastMarkerGroupUpdated($markerGroup);

        return $this->mutationResponse(200, 'Marker was created successfully', [
            'marker' => $marker,
            'markerGroup' => $markerGroup,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name', 'color']);

        $base = $context->base();

        /** @var \App\Models\MarkerGroup $markerGroup */
        $markerGroup = $base->markerGroups()->findOrFail($args['input']['groupId']);

        /** @var \Markers\Models\Marker $marker */
        $marker = $markerGroup->markers()->findOrFail($args['input']['id']);

        $marker->update($data);

        $this->broadcastMarkerGroupUpdated($markerGroup);

        return $this->mutationResponse(200, 'Marker was updated successfully', [
            'marker' => $marker,
            'markerGroup' => $markerGroup,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\MarkerGroup $markerGroup */
        $markerGroup = $base->markerGroups()->findOrFail($args['input']['groupId']);

        /** @var \App\Models\Marker $marker */
        $marker = $markerGroup->markers()->findOrFail($args['input']['id']);

        $pagesFilteringByMarker = $base->pages->filter(function ($page) use ($marker) {
            return collect($page->markerFilters)->contains('markerId', $marker->global_id);
        });

        if ($pagesFilteringByMarker->isNotEmpty()) {
            $message = 'This marker is used to filter pages. Please remove it from the pages first. Page(s): "'.$pagesFilteringByMarker->implode('name', '", "').'"';
            $this->throwValidationException('input.id', $message);
        }

        $marker->delete();

        $this->broadcastMarkerGroupUpdated($markerGroup);

        return $this->mutationResponse(200, 'Marker was deleted successfully', [
            'markerGroup' => $markerGroup,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\MarkerGroup $markerGroup */
        $markerGroup = $base->markerGroups()->findOrFail($args['input']['groupId']);
        /** @var \Markers\Models\Marker $marker */
        $marker = $markerGroup->markers()->findOrFail($args['input']['id']);

        $previousId = $args['input']['previousId'] ?? null;

        if ($previousId) {
            /** @var \Markers\Models\Marker $previousMarker */
            $previousMarker = $markerGroup->markers()
                ->findOrFail($previousId);

            $marker->moveBelow($previousMarker);
        } else {
            $marker->moveToStart();
        }

        $this->broadcastMarkerGroupUpdated($markerGroup);

        return $this->mutationResponse(200, 'Marker was moved successfully', [
            'marker' => $marker,
            'markerGroup' => $markerGroup,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function setMarker($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        /** @var \App\Models\Marker $marker */
        $marker = $base->markers()->findOrFail($args['input']['markerId']);
        $group = $marker->group;

        /** @var \Markers\Models\MarkableModel&\Illuminate\Database\Eloquent\Model $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['markableId']);
        $this->validateMarkable($group, $node);

        $markersFromGroup = null;
        if (! isset($args['input']['context']) && $node instanceof Item) {
            $markersFromGroup = $node->markersFromGroup($group)->getResults();
            if ($markersFromGroup instanceof MarkerCollection) {
                $markers = $markersFromGroup->toArray();
                $args['input']['context'] = $markers[0]['pivot']['context'] ?? null;
            }
        }

        if ($group->type === MarkerType::STATUS) {
            $markersFromGroup = $markersFromGroup ?? $node->markersFromGroup($group)->getResults();
            $node->markersFromGroup($group)->detach($markersFromGroup);
        }
        $query = $node->markersFromGroup($group);

        if ($args['input']['context'] ?? null) {
            $query->wherePivot('context', $args['input']['context']);
        }

        $query->syncWithoutDetaching([$marker->getKey() => ['context' => $args['input']['context'] ?? null]]);
        event(new MarkerAdded($marker, $node));
        if ($node instanceof GloballySearchable) {
            $node->globallySearchable();
        }
        if (\in_array(Searchable::class, class_uses_recursive($node), true)) {
            /** @phpstan-ignore-next-line  */
            $node->instantSearchable();
        }

        $this->broadcastChanges($node);

        return $this->mutationResponse(200, 'The marker was set successfully', [
            'marker' => $marker,
            'node' => $node,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function removeMarker($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        /** @var \App\Models\Marker $marker */
        $marker = $base->markers()->findOrFail($args['input']['markerId']);
        $group = $marker->group;

        /** @var \Markers\Models\MarkableModel&\Illuminate\Database\Eloquent\Model $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['markableId']);

        $this->validateMarkable($group, $node);

        $query = $node->markers();
        if ($args['input']['context'] ?? null) {
            $query->wherePivot('context', $args['input']['context']);
        }
        $query->detach($marker);

        event(new MarkerRemoved($marker, $node));

        if ($node instanceof GloballySearchable) {
            $node->globallySearchable();
        }
        if (\in_array(Searchable::class, class_uses_recursive($node), true)) {
            /** @phpstan-ignore-next-line  */
            $node->instantSearchable();
        }

        $this->broadcastChanges($node);

        return $this->mutationResponse(200, 'The marker was set successfully', [
            'marker' => $marker,
            'node' => $node,
        ]);
    }

    /**
     * @param  array{group: \App\Models\MarkerGroup}  $collection
     */
    public function resolveCollectionType(array $collection): string
    {
        return match ($collection['group']->type) {
            MarkerType::PIPELINE => 'PipelineMarkerCollection',
            MarkerType::TAG => 'TagMarkerCollection',
            MarkerType::STATUS => 'StatusMarkerCollection',
        };
    }

    /**
     * @param  \Markers\Models\MarkableModel&\Illuminate\Database\Eloquent\Model  $rootValue
     *
     * @throws \Exception
     */
    public function resolveCollection(MarkableModel $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($resolveInfo->path, function () {
            return new RelationBatchLoader(new class implements ModelsLoader
            {
                /**
                 * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $parents
                 */
                public function load(EloquentCollection $parents): void
                {
                    $parents->load(['markers.group']);
                }

                /**
                 * @param  \Markers\Models\MarkableModel&\Illuminate\Database\Eloquent\Model  $model
                 */
                public function extract(Model $model): mixed
                {
                    /** @phpstan-ignore-next-line */
                    return $model->markers->groupBy('group.id')
                        ->values()
                        ->map(function (Collection $markers) {
                            /** @var \App\Models\MarkerGroup $group */
                            /** @phpstan-ignore-next-line */
                            $group = $markers->first()->group;
                            $response = [
                                'group' => $group,
                            ];

                            if ($group->type === MarkerType::STATUS) {
                                $response['marker'] = $markers->first();
                            } else {
                                $response['markers'] = $markers->sortBy('added_at');
                                $response['markerCount'] = $markers->count();
                            }

                            return $response;
                        });
                }
            });
        });

        return $instance->load($rootValue);
    }

    protected function validateMarkable(MarkerGroup $group, Model $markable, bool $throw = true): bool
    {
        /** @phpstan-ignore-next-line The mapping has to exist */
        if (($markable instanceof Item) && $markable->mapping->fresh()->markerGroups?->contains('group', $group->id)) {
            return true;
        }

        $markableHasFeature = fn (FeatureListItem $markable, MappingFeatureType $feature) => \in_array(
            $feature,
            /** @phpstan-ignore-next-line Ugh FeatureList has space */
            $markable->list->space->enabledMarkerFeatures($group),
            true
        );
        $allowedType = match (true) {
            $markable instanceof Todo => $markableHasFeature($markable, MappingFeatureType::TODOS),
            $markable instanceof Event => $markableHasFeature($markable, MappingFeatureType::EVENTS),
            $markable instanceof Document => $markableHasFeature($markable, MappingFeatureType::DOCUMENTS),
            $markable instanceof Note => $markableHasFeature($markable, MappingFeatureType::NOTES),
            $markable instanceof Link => $markableHasFeature($markable, MappingFeatureType::LINKS),
            $markable instanceof Pin => $markableHasFeature($markable, MappingFeatureType::PINBOARD),
            default => false,
        };

        if (! $allowedType) {
            if ($throw) {
                $this->throwValidationException('input.markableId', ['The markable ID cannot have the specified tag']);
            }

            return false;
        }

        return true;
    }

    protected function broadcastMarkerGroupUpdated(MarkerGroup $group): void
    {
        Subscription::broadcast('markerGroupUpdated', $this->mutationResponse(
            200,
            'Marker group was updated successfully',
            ['markerGroup' => $group]
        ));
    }
}
