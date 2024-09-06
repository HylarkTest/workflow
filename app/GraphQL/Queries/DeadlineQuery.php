<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use GraphQL\Deferred;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Core\Mutation;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class DeadlineQuery extends Mutation
{
    public function index(Model $root, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        /** @var array<int|string> $path */
        $path = $resolveInfo->path;

        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($path, function () {
            return new RelationBatchLoader(new SimpleModelsLoader('deadlines', fn () => null));
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

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['groupId']);

        $deadline = $deadlineGroup->deadlines()->create($data);

        return $this->mutationResponse(200, 'Deadline was created successfully', [
            'deadline' => $deadline,
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
        $data = Arr::only($args['input'], ['name', 'color']);

        $base = $context->base();

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['groupId']);

        /** @var \Markers\Models\Marker $deadline */
        $deadline = $deadlineGroup->deadlines()->findOrFail($args['input']['id']);

        $deadline->update($data);

        return $this->mutationResponse(200, 'Deadline was updated successfully', [
            'deadline' => $deadline,
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

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['groupId']);

        /** @var \App\Models\Deadline $deadline */
        $deadline = $deadlineGroup->deadlines()->findOrFail($args['input']['id']);

        $deadline->delete();

        return $this->mutationResponse(200, 'Deadline was deleted successfully', [
            'deadlineGroup' => $deadlineGroup,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\DeadlineGroup $deadlineGroup */
        $deadlineGroup = $base->deadlineGroups()->findOrFail($args['input']['groupId']);
        /** @var \App\Models\Deadline $deadline */
        $deadline = $deadlineGroup->deadlines()->findOrFail($args['input']['id']);

        $previousId = $args['input']['previousId'] ?? null;

        if ($previousId) {
            /** @var \App\Models\Deadline $previousDeadline */
            $previousDeadline = $deadlineGroup->deadlines()
                ->findOrFail($previousId);

            $deadline->moveBelow($previousDeadline);
        } else {
            $deadline->moveToStart();
        }

        return $this->mutationResponse(200, 'Deadline was moved successfully', [
            'deadline' => $deadline,
            'deadlineGroup' => $deadlineGroup,
        ]);
    }
}
