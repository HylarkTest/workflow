<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Base;
use App\Models\Mapping;
use Lampager\Paginator;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use App\Models\DeadlineGroup;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\PaginatesQueries;

class DeadlineGroupQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param  array{first: int, after?: string, usedByMapping?: string}  $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $query = $base->deadlineGroups();

        return $this->paginateQuery($query, $args, fn (Paginator $lampager) => $lampager->orderByDesc('id'));
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): DeadlineGroup
    {
        $base = $context->base();

        return $base->deadlineGroups()->findOrFail($args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name', 'description', 'type']);

        $base = $context->base();

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->create($data);

        if (isset($args['input']['deadlines'])) {
            $deadlineGroup->deadlines()->createMany($args['input']['deadlines']);
        }

        if (isset($args['input']['usedByMappings'])) {
            $this->assignToMappings($base, $deadlineGroup, $args['input']['usedByMappings']);
        }

        return $this->mutationResponse(200, 'Deadline group was created successfully', [
            'deadlineGroup' => $deadlineGroup,
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

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['id']);

        $deadlineGroup->update($data);

        if (isset($args['input']['usedByMappings'])) {
            $this->assignToMappings($base, $deadlineGroup, $args['input']['usedByMappings']);
        }

        return $this->mutationResponse(200, 'Deadline group was updated successfully', [
            'deadlineGroup' => $deadlineGroup,
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

        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['id']);

        $deadlineGroup->delete();

        return $this->mutationResponse(200, 'Deadline group was deleted successfully');
    }

    public function usedByMappings(DeadlineGroup $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        $mappings = $base->mappings;

        /** @phpstan-ignore-next-line This is the correct closure definition??? */
        return $mappings->filter(function (Mapping $mapping) use ($rootValue) {
            /** @var \App\Core\Mappings\Features\Feature|null $feature */
            $feature = $mapping->features->find(MappingFeatureType::TIMEKEEPER);

            return ($feature->options['group'] ?? null) === $rootValue->id;
        })->all();
    }

    protected function assignToMappings(Base $base, DeadlineGroup $deadlineGroup, array $mappingIds): void
    {
        $mappings = $base->mappings;
        $globalId = resolve(GlobalId::class);
        $ids = array_map(fn ($id) => (int) $globalId->decodeID($id), $mappingIds);
        /** @var \App\Models\Mapping $mapping */
        foreach ($mappings as $mapping) {
            if (\in_array($mapping->id, $ids, true)) {
                $mapping->enableFeature(MappingFeatureType::TIMEKEEPER, ['group' => $deadlineGroup->id]);
            } elseif (($mapping->features->find(MappingFeatureType::TIMEKEEPER)?->options['group'] ?? null) === $deadlineGroup->id) {
                $mapping->disableFeature(MappingFeatureType::TIMEKEEPER);
            }
        }
    }
}
