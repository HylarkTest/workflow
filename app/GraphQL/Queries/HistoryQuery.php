<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Item;
use Lampager\Paginator;
use Actions\Models\Action;
use App\GraphQL\AppContext;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use LighthouseHelpers\Core\Mutation;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class HistoryQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param array{
     *     onlyLatestActions: bool,
     *     performer?: int,
     *     first: int,
     *     after?: string,
     *     search?: string,
     *     forNode?: string,
     *     type?: string[],
     *     subjectType?: string[],
     *     orderBy?: array<int, array<string, string>>,
     *     collapseChildren: bool,
     *     onlyExistingSubjects: bool,
     * } $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $query = $base->baseActions()->with('performer');

        if ($args['onlyLatestActions']) {
            $query->where('is_latest', true);
        }

        if ($args['collapseChildren']) {
            $query->whereNull('parent_id');
        }

        if (isset($args['performer'])) {
            $query->where('performer_id', $args['performer']);
        }

        if ($args['onlyExistingSubjects'] && ! isset($args['forNode']) && ! isset($args['subjectType'])) {
            $this->throwValidationException('onlyExistingSubjects', 'Either forNode or subjectType must be set when onlyExistingSubjects is true');
        }

        if (isset($args['forNode'])) {
            $node = Utils::resolveModelFromGlobalId($args['forNode']);
            if ($node->getAttribute('base_id') !== $base->getKey()) {
                throw (new ModelNotFoundException)->setModel($node::class, $node->getKey());
            }
            $query->whereMorphRelation('subject', $node->getMorphClass(), $node->getQualifiedKeyName(), $node->getKey());

            if ($args['onlyExistingSubjects'] && method_exists($node, 'getDeletedAtColumn')) {
                $query->whereMorphRelation('subject', $node->getMorphClass(), $node->getDeletedAtColumn(), '=', null);
            }
        } elseif (isset($args['subjectType'])) {
            $subjectTypes = $args['subjectType'];
            $query->where(function ($query) use ($subjectTypes, $args) {
                [$itemTypes, $otherTypes] = collect($subjectTypes)->partition(fn ($subjectType) => str_starts_with($subjectType, 'Item:'));
                if ($itemTypes->isNotEmpty()) {
                    $mappingIds = $itemTypes->map(function ($subjectType) {
                        $mappingGlobalId = substr($subjectType, 5);

                        return resolve(GlobalId::class)->decodeID($mappingGlobalId);
                    });
                    $itemModel = new Item;
                    $query->orWhereHasMorph('subject', $itemModel->getMorphClass(), function ($query) use ($itemModel, $mappingIds, $args) {
                        $query->whereIn($itemModel->qualifyColumn('mapping_id'), $mappingIds->all());
                        if ($args['onlyExistingSubjects']) {
                            $query->whereNull($itemModel->getDeletedAtColumn());
                        }
                    });
                }
                if ($otherTypes->isNotEmpty()) {
                    $query->orWhere(function ($query) use ($subjectTypes, $args) {
                        $morphTypes = collect($subjectTypes)
                            ->map(function ($type) {
                                /** @var class-string<\Illuminate\Database\Eloquent\Model> $class */
                                $class = Utils::namespaceModelClass($type);

                                return new $class;
                            });
                        $query->whereIn('subject_type', $morphTypes->map->getMorphClass()->all());
                        if ($args['onlyExistingSubjects']) {
                            $softDeletableMorphTypes = $morphTypes->filter(function ($morphType) {
                                return method_exists($morphType, 'getDeletedAtColumn');
                            });
                            $query->whereHasMorph('subject', $softDeletableMorphTypes->map->getMorphClass()->all(), function ($query, $morphType) {
                                /** @phpstan-ignore-next-line Ensured in the previous filter */
                                $column = (new $morphType)->getDeletedAtColumn();
                                $query->whereNull($column);
                            });
                        }
                    });
                }
            });
        }

        $allHistoryCount = $query->count();

        $historyStartDate = $context->base()->accountLimits()->getHistoryLogStartDate();
        if ($historyStartDate) {
            $historyCountForPlan = (clone $query)->where('created_at', '>', $historyStartDate)->count();
        } else {
            $historyCountForPlan = $allHistoryCount;
        }

        if ($args['search'] ?? null) {
            $query->where('subject_name', ilike(), $args['search'].'%');
        }

        if (isset($args['type'])) {
            $query->whereIn('type', $args['type']);
        }

        $filteredHistoryCount = $query->count();

        if ($historyStartDate) {
            $query->where('created_at', '>', $historyStartDate);
        }

        $promise = $this->paginateQuery($query, $args, function (Paginator $lampager) use ($args) {
            $order = $args['orderBy'] ?? [
                ['field' => 'CREATED_AT', 'direction' => 'desc'],
            ];
            foreach ($order as $orderByClause) {
                $lampager->orderBy(
                    Str::snake(mb_strtolower($orderByClause['field'])),
                    mb_strtolower($orderByClause['direction'])
                );
            }
            $lampager->forward();
        });

        return $promise->then(function ($paginator) use ($allHistoryCount, $historyCountForPlan, $filteredHistoryCount) {
            $paginator->meta = [
                'allHistoryCount' => $allHistoryCount,
                'allowedHistoryCount' => $historyCountForPlan,
                'filteredHistoryCount' => $filteredHistoryCount,
            ];

            return $paginator;
        });
    }

    /**
     * @param array{
     *     includeChildren: bool
     * } $args
     *
     * @throws \JsonException
     */
    public function resolveChanges(Action $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): null|array|SyncPromise
    {
        $includeChildren = $args['includeChildren'];

        if (! $includeChildren) {
            return $rootValue->changes();
        }

        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'childActions';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(new SimpleModelsLoader('childActions', fn () => null)),
        )->load($rootValue)->then(function ($childActions) use ($rootValue) {
            return [
                ...($rootValue->changes() ?? []),
                ...$childActions->map(fn (Action $action) => [
                    'description' => $action->description(false),
                    'type' => 'line',
                ]),
            ];
        });
    }

    public function resolveCreateAction(Model $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'createAction';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(new SimpleModelsLoader('createAction', fn () => null)),
        )->load($rootValue);
    }

    public function resolveLatestAction(Model $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'latestAction';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(new SimpleModelsLoader('latestAction', fn () => null)),
        )->load($rootValue);
    }
}
