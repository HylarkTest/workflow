<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Mappings\Models\Category;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;

class CategoryQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param  array{first: int, after?: string, type?: \Markers\Core\MarkerType}  $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $query = $base->categories();

        return $this->paginateQuery($query, $args);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Category
    {
        $base = $context->base();

        return $base->categories()->findOrFail($args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $base = $context->base();

        if (! $base->accountLimits()->canCreateCategories()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $category = $base->categories()->create($data);

        if (isset($args['input']['items'])) {
            $category->items()->createMany($args['input']['items']);
        }

        return $this->mutationResponse(200, 'Category was created successfully', [
            'category' => $category,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $base = $context->base();

        $category = $base->categories()->findOrFail($args['input']['id']);

        $category->update($data);

        return $this->mutationResponse(200, 'Category was updated successfully', [
            'category' => $category,
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

        $category = $base->categories()->findOrFail($args['input']['id']);

        $category->delete();

        return $this->mutationResponse(200, 'Category was deleted successfully');
    }
}
