<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Base;
use App\Models\Page;
use App\Models\Space;
use App\Models\Mapping;
use Lampager\Paginator;
use App\GraphQL\AppContext;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Markers\Core\MarkerType;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Core\Preferences\SpacePreferences;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class MarkerGroupQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param array{
     *     first: int,
     *     after?: string,
     *     types?: \Markers\Core\MarkerType[],
     *     usedByMappings?: string[],
     *     usedByFeatures?: string[],
     *     spaceIds?: int[],
     * } $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $query = $base->markerGroups();

        if (isset($args['types'])) {
            $query->whereIn('type', $args['types']);
        }

        if (isset($args['usedByMappings'])) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mapping> $mappings */
            $mappings = $base->mappings()->findOrFail($args['usedByMappings']);
            $groupIds = $mappings->flatMap(fn (Mapping $mapping) => $mapping->markerGroups?->pluck('group') ?? []);
            $query->whereKey($groupIds);
        }

        if (isset($args['usedByFeatures'])) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Space> $spaces */
            $spaces = ($args['spaceIds'] ?? []) ? $base->spaces()->findOrFail($args['spaceIds']) : $base->spaces;
            $markerGroupIds = $spaces->flatMap->markerGroupsWithEnabledFeatures(
                array_map(fn (string $feature) => MappingFeatureType::from($feature), $args['usedByFeatures'])
            );
            $query->whereKey($markerGroupIds);
        }

        return $this->paginateQuery($query, $args, fn (Paginator $lampager) => $lampager->orderByDesc('id'));
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): MarkerGroup
    {
        $base = $context->base();

        return $base->markerGroups()->findOrFail($args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name', 'description', 'type']);

        $type = MarkerType::from($data['type']);

        $base = $context->base();

        if (! $base->accountLimits()->canCreateMarkers($type)) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $markerGroup = $base->markerGroups()->create($data);

        if (isset($args['input']['markers'])) {
            $markerGroup->markers()->createMany($args['input']['markers']);
        }

        if (isset($args['input']['usedByMappings'])) {
            $this->assignToMappings($base, $markerGroup, $args['input']['usedByMappings']);
        }

        if (isset($args['input']['usedByFeatures'])) {
            $this->assignToFeatures($base, $markerGroup, $args['input']['usedByFeatures']);
        }

        return $this->mutationResponse(200, 'Marker group was created successfully', [
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
        $data = Arr::only($args['input'], ['name', 'description']);
        if (isset($args['input']['usedByFeatures'])) {
            $data['features'] = $args['input']['usedByFeatures'];
        }

        $base = $context->base();

        /** @var \App\Models\MarkerGroup $markerGroup */
        $markerGroup = $base->markerGroups()->findOrFail($args['input']['id']);

        // Doing this first in case it throws an error
        if (isset($args['input']['usedByMappings'])) {
            $this->assignToMappings($base, $markerGroup, $args['input']['usedByMappings']);
        }

        $markerGroup->update($data);

        if (isset($args['input']['usedByFeatures'])) {
            $this->assignToFeatures($base, $markerGroup, $args['input']['usedByFeatures']);
        }

        return $this->mutationResponse(200, 'Marker group was updated successfully', [
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

        $markerGroup = $base->markerGroups()->findOrFail($args['input']['id']);
        $markers = $markerGroup->markers;

        $pagesFilteringByGroup = $base->pages->filter(function (Page $page) use ($markers) {
            return collect($page->markerFilters)
                ->contains(fn ($filter) => $markers->contains('global_id', $filter['markerId']));
        });

        if ($pagesFilteringByGroup->isNotEmpty()) {
            $message = 'This marker group is used to filter pages. Please remove it from the pages first. Page(s): "'.$pagesFilteringByGroup->implode('name', '", "').'"';
            $this->throwValidationException('input.id', $message);
        }

        $markerGroup->delete();

        return $this->mutationResponse(200, 'Marker group was deleted successfully');
    }

    public function usedByMappings(MarkerGroup $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        $mappings = $base->mappings;

        /** @phpstan-ignore-next-line This is the correct closure definition??? */
        return $mappings->filter(function (Mapping $mapping) use ($rootValue) {
            return (bool) $mapping->markerGroups?->contains('group', $rootValue->getKey());
        })->all();
    }

    public function usedByFeatures(MarkerGroup $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        $spaces = $base->spaces;

        /** @phpstan-ignore-next-line This is the correct closure definition */
        return $spaces->map(function (Space $space) use ($rootValue) {
            return [
                'space' => $space,
                'features' => array_map(
                    fn (MappingFeatureType $feature) => $feature->value,
                    $space->settings->markerGroups[$rootValue->getKey()] ?? []
                ),
            ];
        })->all();
    }

    protected function assignToMappings(Base $base, MarkerGroup $markerGroup, array $mappingIds): void
    {
        $mappings = $base->mappings;
        $globalId = resolve(GlobalId::class);
        $ids = array_map(fn ($id) => (int) $globalId->decodeID($id), $mappingIds);

        /** @var \App\Models\Mapping $mapping */
        foreach ($mappings as $mapping) {
            if (! \in_array($mapping->id, $ids, true)) {
                $mapping->validateRemovingMarkerGroup($markerGroup, 'input.usedByMappings');
            }
        }
        /** @var \App\Models\Mapping $mapping */
        foreach ($mappings as $mapping) {
            if (\in_array($mapping->id, $ids, true)) {
                if (! $mapping->markerGroups?->contains('group', $markerGroup->getKey())) {
                    $mapping->addMarkerGroup($markerGroup);
                }
            } elseif ($mapping->markerGroups?->contains('group', $markerGroup->getKey())) {
                $mapping->removeMarkerGroup($markerGroup);
            }
            Subscription::broadcast('mappingUpdated', $this->mutationResponse(
                200,
                'Mapping was updated successfully',
                ['mapping' => $mapping],
            ));
        }
    }

    /**
     * @param array<array{
     *     spaceId: string,
     *     features: string[]
     * }> $featuresArr
     */
    protected function assignToFeatures(Base $base, MarkerGroup $markerGroup, array $featuresArr): void
    {
        $spaces = $base->spaces;
        $features = collect($featuresArr);
        foreach ($spaces as $space) {
            $spaceId = $space->id;
            if (! $features->contains('spaceId', $spaceId)) {
                $space->updatePreferences(function (SpacePreferences $preferences) use ($markerGroup) {
                    $preferences->markerGroups = Arr::except($preferences->markerGroups, $markerGroup->getKey());
                });
            } else {
                /** @var array<string> $spaceFeatures */
                /** @phpstan-ignore-next-line Previous if guaranteed this is not null */
                $spaceFeatures = $features->firstWhere('spaceId', $spaceId)['features'];
                $space->updatePreferences(function (SpacePreferences $preferences) use ($markerGroup, $spaceFeatures) {
                    $preferences->markerGroups[(int) $markerGroup->getKey()] = array_map(
                        fn (string $feature) => MappingFeatureType::from($feature),
                        $spaceFeatures
                    );
                });
            }
        }
    }
}
