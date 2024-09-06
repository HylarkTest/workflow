<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Category;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class CategoryItemQuery extends Mutation
{
    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $base = $context->base();

        /** @var \App\Models\Category $category */
        $category = $base->categories()->findOrFail($args['input']['categoryId']);

        $item = $category->items()->create($data);

        $this->broadcastCategoryUpdated($category);

        return $this->mutationResponse(200, 'Category item was created successfully', [
            'item' => $item,
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

        /** @var \App\Models\Category $category */
        $category = $base->categories()->findOrFail($args['input']['categoryId']);

        /** @var \Mappings\Models\CategoryItem $item */
        $item = $category->items()->findOrFail($args['input']['id']);

        $item->update($data);

        $this->broadcastCategoryUpdated($category);

        return $this->mutationResponse(200, 'Category item was updated successfully', [
            'item' => $item,
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

        /** @var \App\Models\Category $category */
        $category = $base->categories()->findOrFail($args['input']['categoryId']);

        /** @var \Mappings\Models\CategoryItem $item */
        $item = $category->items()->findOrFail($args['input']['id']);

        $item->delete();

        $this->broadcastCategoryUpdated($category);

        return $this->mutationResponse(200, 'Category item was deleted successfully', [
            'category' => $category,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Category $category */
        $category = $base->categories()->findOrFail($args['input']['categoryId']);
        /** @var \Mappings\Models\CategoryItem $item */
        $item = $category->items()->findOrFail($args['input']['id']);

        $previousId = $args['input']['previousId'] ?? null;

        if ($previousId) {
            /** @var \Mappings\Models\CategoryItem $previousItem */
            $previousItem = $category->items()
                ->findOrFail($previousId);

            $item->moveBelow($previousItem);
        } else {
            $item->moveToStart();
        }
        $this->broadcastCategoryUpdated($category);

        return $this->mutationResponse(200, 'Category item was moved successfully', [
            'item' => $item,
            'category' => $category,
        ]);
    }

    protected function broadcastCategoryUpdated(Category $category): void
    {
        Subscription::broadcast('categoryUpdated', $this->mutationResponse(
            200,
            'Category was updated successfully',
            ['category' => $category]
        ));
    }
}
