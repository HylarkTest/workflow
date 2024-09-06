<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Base;
use App\Models\Item;
use GraphQL\Deferred;
use App\GraphQL\AppContext;
use LighthouseHelpers\Utils;
use App\Models\AssigneeGroup;
use App\Models\Contracts\Assignable;
use LighthouseHelpers\Core\Mutation;
use App\Models\Contracts\FeatureList;
use GraphQL\Type\Definition\ResolveInfo;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Collection;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\BroadcastsChanges;
use LighthouseHelpers\Exceptions\ValidationException;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

/**
 * @template TList of FeatureList
 * @template TItem of FeatureListItem
 */
class AssigningQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\BroadcastsChanges<TList, TItem>
     */
    use BroadcastsChanges;

    /**
     * @param array{
     *     input: array{
     *         assignableId: string,
     *         assigneeGroups: array<array{
     *             groupId: int,
     *             assignees: int[]
     *         }>
     *     }
     * } $args
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    public function updateAssignees(null $root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        $assignableId = $args['input']['assignableId'];
        /**
         * @var \App\Models\Item|\App\Models\Contracts\FeatureListItem<TList, TItem> $assignable
         */
        $assignable = Utils::resolveModelFromGlobalId($assignableId);

        if (! $assignable instanceof Assignable) {
            throw ValidationException::withMessages(['assignableId' => 'The given node is not assignable']);
        }

        $assigneeInfo = collect($args['input']['assigneeGroups']);
        $members = $base->members()
            ->wherePivotIn('id', $assigneeInfo->pluck('assignees')->collapse())
            ->get()
            ->keyBy('pivot.id');

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssigneeGroup> $groups */
        $groups = $base->assigneeGroups->keyBy('id');

        $groups->each(function (AssigneeGroup $group) use ($assigneeInfo, $members, $assignable) {
            $inputGroup = $assigneeInfo->firstWhere('groupId', $group->id);

            if ($inputGroup) {
                /** @phpstan-ignore-next-line */
                $syncIds = collect($inputGroup['assignees'])->mapWithKeys(function (int $id) use ($members, $group) {
                    $member = $members->get($id);

                    return isset($member->pivot) ? [$member->pivot->id => ['group_id' => $group->id]] : null;
                })
                    ->filter()
                    ->all();
                $assignable
                    ->assigneesForGroup($group)
                    ->sync($syncIds);
            } else {
                $assignable
                    ->assigneesForGroup($group)
                    ->sync([]);
            }
        });

        $this->broadcastChanges($assignable);

        return $this->mutationResponse(200, 'Assignees updated', ['node' => $assignable]);
    }

    /**
     * @param  \App\Models\Item|\App\Models\Contracts\FeatureListItem<TList, TItem>  $root
     */
    public function resolveAssignees(Item|FeatureListItem $root, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();
        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($resolveInfo->path, function () {
            return new RelationBatchLoader(new SimpleModelsLoader('assignees', fn () => null));
        });

        $assigneeGroups = $base->assigneeGroups->keyBy('id');

        /** @phpstan-ignore-next-line */
        return $instance->load($root)->then(function (Collection $assignees) use ($assigneeGroups) {
            $groupedAssignees = $assignees->groupBy('pivot.group_id');

            return $groupedAssignees->map(function (Collection $assignees, $groupId) use ($assigneeGroups) {
                $group = $assigneeGroups->get($groupId);

                return [
                    'group' => $group,
                    'assignees' => $assignees,
                ];
            })->values();
        });
    }

    public function resolveAssigneeGroups(Base $root, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        return BatchLoaderRegistry::instance($resolveInfo->path, function () {
            return new RelationBatchLoader(new SimpleModelsLoader('assigneeGroups', fn () => null));
        })->load($root);
    }

    public function resolveAssigneeGroupMembers(AssigneeGroup $group, array $args, AppContext $context, \Nuwave\Lighthouse\Execution\ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        return resolve(BaseQuery::class)->resolveMembers($base, $args, $context, $resolveInfo);
    }
}
