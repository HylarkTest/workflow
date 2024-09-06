<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\AppContext;
use LighthouseHelpers\Utils;
use Lampager\Laravel\Paginator;
use LighthouseHelpers\Core\Mutation;
use PHPStan\ShouldNotHappenException;
use App\Models\Contracts\SavedFilterModel;
use Illuminate\Contracts\Database\Query\Builder;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use LighthouseHelpers\Exceptions\ModelNotFoundException;

class SavedFilterQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param array{
     *     nodeId?: string,
     *     privacy: 'ONLY_PRIVATE' | 'ONLY_PUBLIC' | 'ALL',
     *     first: int,
     *     after: string,
     *     page?: int,
     *     search?: string,
     * } $args
     */
    public function index($rootValue, array $args, AppContext $context): SyncPromise
    {
        if ($args['nodeId'] ?? null) {
            $node = Utils::resolveModelFromGlobalId($args['nodeId']);
            if (! $node instanceof SavedFilterModel) {
                throw new ModelNotFoundException('The node does not support saved filters');
            }
            $query = $node->savedFilters();
        } else {
            $query = $context->base()->savedFilters();
        }

        if ($args['search'] ?? null) {
            $query->where('name', ilike(), "%{$args['search']}%");
        }

        match ($args['privacy']) {
            'ONLY_PRIVATE' => $query->where('base_user_id', $context->baseUser()->id),
            'ONLY_PUBLIC' => $query->whereNull('base_user_id'),
            'ALL' => $query->where(function (Builder $query) use ($context) {
                $query->whereNull('base_user_id')
                    ->orWhere('base_user_id', $context->baseUser()->id);
            }),
        };

        return $this->paginateQuery($query, $args, fn (Paginator $query) => $query->orderByDesc('id'));
    }

    /**
     * @param  null  $rootValue
     */
    public function store($rootValue, array $args, AppContext $context): array
    {
        $nodeId = $args['input']['nodeId'];
        $node = Utils::resolveModelFromGlobalId($nodeId);

        if (! $node instanceof SavedFilterModel || ! $node->canSaveFilters()) {
            throw new ModelNotFoundException('The node does not support saved filters');
        }

        $filter = $node->savedFilters()->create([
            'name' => $args['input']['name'],
            'filters' => $args['input']['filters'] ?? null,
            'order_by' => $args['input']['orderBy'] ?? null,
            'group' => $args['input']['group'] ?? null,
            'base_user_id' => ($args['input']['private'] ?? false)
                ? $context->baseUser()->id
                : null,
        ]);

        return $this->mutationResponse(200, 'Filter saved successfully', [
            'savedFilter' => $filter,
        ]);
    }

    /**
     * @param  null  $rootValue
     * @param array{
     *     input: array{
     *         id: string,
     *         name?: string,
     *         filter?: array,
     *         orderBy?: array,
     *         group?: string,
     *     }
     * } $args
     */
    public function update($rootValue, array $args, AppContext $context): array
    {
        $id = $args['input']['id'];
        $filter = $context->base()->savedFilters()->findOrFail($id);

        $data = collect($args['input'])
            ->forget('id')
            ->mapWithKeys(function ($value, $key) {
                return match ($key) {
                    'name',
                    'filters',
                    'orderBy',
                    'group' => [$key => $value],
                    default => throw new ShouldNotHappenException("Unexpected key: $key"),
                };
            });

        $filter->update($data->all());

        return $this->mutationResponse(200, 'Filter updated successfully', [
            'savedFilter' => $filter,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $id = $args['input']['id'];
        $filter = $context->base()->savedFilters()->findOrFail($id);

        $filter->delete();

        return $this->mutationResponse(200, 'Filter deleted successfully', [
            'savedFilter' => $filter,
        ]);
    }
}
