<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Items;

use App\Models\Item;
use App\GraphQL\AppContext;
use Markers\Core\MarkerType;
use LighthouseHelpers\Core\Mutation;
use PHPStan\ShouldNotHappenException;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class ItemMarkerGroupQuery extends Mutation
{
    public function index(Item $root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Mapping $mapping */
        $mapping = $root->mapping;

        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup|null $markerGroup */
        $markerGroup = $mapping->markerGroups?->find($resolveInfo->fieldName);
        abort_if(! $markerGroup, 404);

        $response = $markerGroup->toArray();

        $response['value'] = function () use ($markerGroup, $resolveInfo, $root) {
            $groupId = $markerGroup->group;

            $relationName = $markerGroup->type === MarkerType::STATUS ? 'marker' : 'markers';

            /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
            $instance = BatchLoaderRegistry::instance($resolveInfo->path, function () use ($relationName, $groupId) {
                return new RelationBatchLoader(
                    new SimpleModelsLoader($relationName, fn (/* @var \Illuminate\Database\Eloquent\Builder $query */ $query) => $query->fromGroup($groupId))
                );
            });

            return $instance->load($root);
        };

        return $response;
    }

    /**
     * @param  null  $root
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function store($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \Markers\Models\MarkerGroup $group */
        $group = $base->markerGroups()->findOrFail($args['directive'][0]);

        /** @var \Markers\Models\Collections\MarkerCollection $markers */
        $markers = $group->markers()->findOrFail($args['input']['markers']);

        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($args['input']['item']);

        if ($relationName = ($args['directive'][1] ?? null)) {
            $related = $args['input']['related'];
            if ($related === null) {
                throw new ShouldNotHappenException('If there is a relation name then related is always included');
            }
            $related = $this->getRelatedItemsQuery($item, $relationName, $related)->get();
            $related->each(fn (Model $related) => $related->getRelation('pivot')
                ->markers()->syncWithoutDetaching($markers));
        } else {
            $item->markers()->syncWithoutDetaching($markers);
        }

        return $this->mutationResponse(200, 'The markers were added successfully', [
            'item' => $item,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function destroy($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \Markers\Models\MarkerGroup $group */
        $group = $base->markerGroups()->findOrFail($args['directive'][0]);

        /** @var \Markers\Models\Collections\MarkerCollection|null $markers */
        $markers = isset($args['input']['markers']) ? $group->markers()->findOrFail($args['input']['markers']) : null;

        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($args['input']['item']);

        if ($relationName = ($args['directive'][1] ?? null)) {
            $related = $args['input']['related'];
            if ($related === null) {
                throw new ShouldNotHappenException('If there is a relation name then related is always included');
            }
            // @phpstan-ignore-next-line Scope is added in MarkergablePivotScope
            $related = $this->getRelatedItemsQuery($item, $relationName, $related)
                ->withPivotMarkersFromGroup($group)->get();
            $related->each(function (Model $model) use ($markers, $group) {
                $query = $model->getRelation('pivot')->markersFromGroup($group);
                if ($markers) {
                    $query->detach($markers);
                } else {
                    $query->detach();
                }
            });
        } else {
            $markerQuery = $item->markersFromGroup($group);

            if (! $markers) {
                $markerQuery->detach();
            } else {
                $markerQuery->detach($markers);
            }
        }

        return $this->mutationResponse(200, 'The markers were removed successfully', [
            'item' => $item,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function update($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \Markers\Models\MarkerGroup $group */
        $group = $base->markerGroups()->findOrFail($args['directive'][0]);

        /** @var \Markers\Models\Collections\MarkerCollection $marker */
        $marker = $group->markers()->findOrFail($args['input']['marker']);

        /** @var \App\Models\Item $item */
        $item = $base->items()->findOrFail($args['input']['item']);

        if ($relationName = ($args['directive'][1] ?? null)) {
            $related = $args['input']['related'];

            if ($related === null) {
                throw new ShouldNotHappenException('If there is a relation name then related is always included');
            }
            $related = $this->getRelatedItemsQuery($item, $relationName, $related)->get();
            $related->each(static function (Model $model) use ($marker) {
                $model->getRelation('pivot')->markers()->sync($marker);
            });
        } else {
            $item->markers()->sync($marker);
        }

        return $this->mutationResponse(200, 'The marker was set successfully', [
            'item' => $item,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Markers\Models\DummyModelWithMarkers>
     */
    protected function getRelatedItemsQuery(Item $item, string $relationName, array $related): BelongsToMany
    {
        return $item->{$relationName}()->whereGlobalId($related);
    }
}
