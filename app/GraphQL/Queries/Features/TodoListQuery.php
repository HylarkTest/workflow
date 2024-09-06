<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use GraphQL\Deferred;
use App\Models\TodoList;
use App\GraphQL\AppContext;
use LighthouseHelpers\Utils;
use App\GraphQL\CountModelsLoader;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\Features\Repositories\TodoListRepository;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\TodoList>
 */
class TodoListQuery extends FeatureListQuery
{
    public function resolveIncompleteCount(TodoList $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        $forMapping = $args['forMapping'] ?? null;
        /** @var \App\Models\Mapping|null $mapping */
        $mapping = $forMapping ? $context->base()->mappings->find($forMapping) : null;
        $node = $args['forNode'] ?? null;
        if ($node) {
            $node = Utils::resolveModelFromGlobalId($node);
        }

        return BatchLoaderRegistry::instance(
            array_merge(
                $resolveInfo->path,
                ['count']
            ),
            static function () use ($mapping, $node): RelationBatchLoader {
                return new RelationBatchLoader(
                    new CountModelsLoader('incompleteTodos', function (HasMany|Builder $query) use ($mapping, $node) {
                        if ($mapping) {
                            $query->whereRelation('items', 'mapping_id', $mapping->id);
                        }
                        if ($node) {
                            $query->whereRelation('items', 'items.id', $node->getKey());
                        }
                    })
                );
            }
        )->load($rootValue);
    }

    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultTodoLists();
    }

    protected function repository(): TodoListRepository
    {
        return resolve(TodoListRepository::class);
    }

    protected function getListKey(): string
    {
        return 'todoList';
    }
}
