<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Models\Item;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Core\Mutation;
use Lampager\Laravel\PaginationResult;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Models\Contracts\FeatureListItem;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Features\Repositories\FeatureItemRepository;

/**
 * @template TItem of \App\Models\Contracts\FeatureListItem&\Illuminate\Database\Eloquent\Model
 * @template TList of \App\Models\Contracts\FeatureList&\Illuminate\Database\Eloquent\Model
 */
abstract class FeatureListItemQuery extends Mutation
{
    /**
     * @param  ?\App\Models\Item  $rootValue
     * @param array{
     *     first: int,
     *     after?: string,
     *     forNode?: string,
     *     forLists?: int[],
     *     forMapping?: String,
     *     spaceId?: String,
     *     search?: string,
     *     orderBy?: array,
     *     filter?: string,
     *     markers?: string[],
     *     fileTypes?: string[],
     *     group?: string,
     * } $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): PaginationResult|array|SyncPromise
    {
        if (isset($args['forLists'])) {
            $listIds = $args['forLists'];
        } elseif (isset($args[$this->getListKey().'Id'])) {
            $listIds = [(int) $args[$this->getListKey().'Id']];
        } else {
            $listIds = null;
        }

        return $this->repository()->paginateFeatureItems(
            base: $context->base(),
            /** @phpstan-ignore-next-line This produces the correct array */
            paginationArgs: Arr::only($args, ['first', 'after']),
            listIds: $listIds,
            node: $rootValue && $rootValue instanceof Item ? $rootValue : ($args['forNode'] ?? null),
            mapping: isset($args['forMapping']) ? (int) $args['forMapping'] : null,
            space: isset($args['spaceId']) ? (int) $args['spaceId'] : null,
            filters: $this->getFilterArgs($args),
            orderBy: $args['orderBy'] ?? [],
            group: $args['group'] ?? null,
        );
    }

    /**
     * @param  ?\App\Models\Item  $rootValue
     *
     * @throws \Exception
     */
    public function stats($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        return $this->repository()->getFeatureStats(
            $context->base(),
            $args['forLists'] ?? null,
            $rootValue && $rootValue instanceof Item ? $rootValue : ($args['forNode'] ?? null),
            isset($args['forMapping']) ? (int) $args['forMapping'] : null,
        );
    }

    /**
     * @param  null  $rootValue
     * @return TItem
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): FeatureListItem
    {
        return $this->repository()->getFeatureItem($context->base(), $args['id']);
    }

    /**
     * @param  null  $rootValue
     * @param array{
     *     input: array{
     *         markers?: array<array{
     *             groupId: int,
     *             markers: int[],
     *         }>,
     *         associations?: int[],
     *         assignees?: array<array{
     *             groupId: int,
     *             assignees: int[],
     *         }>,
     *     }
     * } $args
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = $this->getCreateData($args['input']);
        $listIdKey = $this->getListKey().'Id';
        /** @phpstan-ignore-next-line Not sure how to add dynamic type to array shape */
        $data[$listIdKey] = $args['input'][$listIdKey];
        $base = $context->base();

        $this->validateData($base, $data);

        $item = $this->repository()->createFeatureItem($base, $data);

        return $this->mutationResponse(200, $this->getSuccessMessage('created'), [
            $this->getItemKey() => $item,
            /** @phpstan-ignore-next-line This property is in the interface */
            $this->getListKey() => $item->list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = $this->getUpdateData($args['input']);
        $listIdKey = $this->getListKey().'Id';
        if (isset($args['input'][$listIdKey])) {
            $data[$listIdKey] = $args['input'][$listIdKey];
        }

        $base = $context->base();
        $this->validateData($base, $data);

        $item = $this->repository()->updateFeatureItem($base, $args['input']['id'], $data);

        return $this->mutationResponse(200, $this->getSuccessMessage('updated'), [
            $this->getItemKey() => $item,
            /** @phpstan-ignore-next-line This property is in the interface */
            $this->getListKey() => $item->list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function duplicate($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $item = $this->repository()->duplicateFeatureItem($base, $args['input']['id'], $args['input']);

        return $this->mutationResponse(200, $this->getSuccessMessage('duplicated'), [
            $this->getItemKey() => $item,
            /** @phpstan-ignore-next-line This property is in the interface */
            $this->getListKey() => $item->list,
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

        $item = $this->repository()->getFeatureItem($base, $args['input']['id']);

        /** @phpstan-ignore-next-line This property is in the interface */
        $list = $item->list;

        $this->repository()->deleteFeatureItem($base, $args['input']['id'], $args['input']);

        return $this->mutationResponse(200, $this->getSuccessMessage('deleted'), [
            $this->getListKey() => $list,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function restore($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $item = $this->repository()->restoreFeatureItem($base, (int) $args['input']['id']);

        if ($item) {
            return $this->mutationResponse(200, $this->getSuccessMessage('restored'), [
                $this->getItemKey() => $item,
                /** @phpstan-ignore-next-line This property is in the interface */
                $this->getListKey() => $item->list,
            ]);
        }

        return $this->mutationResponse(400, $this->getFailureMessage('restored'));
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $itemName = $this->getItemKey();
        $items = Str::plural($itemName);
        $itemId = $args['input']['id'];

        if (! is_numeric($itemId)) {
            $itemId = resolve(GlobalId::class)->decodeID($itemId);
        }
        $thisItem = $base->$items()->findOrFail($itemId);

        $listName = $this->getListKey();
        $originalList = $thisItem->$listName;

        $lists = Str::plural($listName);
        $listId = $args['input'][$listName.'Id'];
        $thisList = $base->$lists()->where('space_id', $originalList->space_id)->findOrFail($listId);

        $parent_id = $listName.'_id';
        if ($thisList->id !== $thisItem->$parent_id) {
            $thisItem->$listName()->associate($thisList)->save();
        }

        if ($thisItem->order) {
            $previousId = $args['input']['previousId'] ?? null;
            if ($previousId) {
                $previousTodo = $thisList->$items()->findOrFail($previousId);
                $thisItem->moveBelow($previousTodo);
            } else {
                $thisItem->moveToStart();
            }
        }

        return $this->mutationResponse(200, ucfirst($itemName).' was moved successfully', [
            $itemName => $thisItem,
            $listName => $thisList,
        ]);
    }

    protected function getFilterArgs(array $args): array
    {
        return Arr::only($args, $this->filterArgKeys());
    }

    protected function filterArgKeys(): array
    {
        return ['filters', 'includeGroups', 'excludeGroups', 'search', 'isFavorited'];
    }

    protected function getSuccessMessage(string $action): string
    {
        return ucfirst(Str::slug($this->getItemKey(), ' '))." was $action successfully.";
    }

    protected function getFailureMessage(string $action): string
    {
        return ucfirst(Str::slug($this->getItemKey(), ' '))." could not be $action successfully.";
    }

    protected function getCreateData(array $input): array
    {
        return Arr::only($input, [
            'markers',
            'associations',
            'assigneeGroups',
            ...$this->getCreateDataKeys(),
        ]);
    }

    protected function getUpdateData(array $input): array
    {
        return Arr::only($input, $this->getUpdateDataKeys());
    }

    protected function getCreateDataKeys(): array
    {
        return [];
    }

    protected function getUpdateDataKeys(): array
    {
        return [];
    }

    /**
     * @return \App\Core\Features\Repositories\FeatureItemRepository<TItem, TList>
     */
    abstract protected function repository(): FeatureItemRepository;

    abstract protected function getListKey(): string;

    abstract protected function getItemKey(): string;

    abstract protected function validateData(Base $base, array $data): void;
}
