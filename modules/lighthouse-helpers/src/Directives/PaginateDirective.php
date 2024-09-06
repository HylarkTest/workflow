<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Support\Utils;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Pagination\Cursor;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Pagination\PaginationArgs;
use Nuwave\Lighthouse\Pagination\PaginationType;
use LighthouseHelpers\Pagination\CursorProcessor;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use LighthouseHelpers\Directives\Concerns\HasDefaultPaginationArguments;
use Nuwave\Lighthouse\Pagination\PaginateDirective as BasePaginateDirective;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class PaginateDirective extends BasePaginateDirective
{
    use HasDefaultPaginationArguments;

    /**
     * Resolve the field directive.
     * Overriding the parent method in order to implement proper cursor
     * pagination.
     */
    public function resolveField(FieldValue $fieldValue): callable
    {
        return function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
            if ($this->directiveHasArgument('builder')) {
                /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel> $query */
                $query = \call_user_func(
                    $this->getResolverFromArgument('builder'),
                    $root,
                    $args,
                    $context,
                    $resolveInfo
                );
            } else {
                /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel> $query */
                $query = $this->getModelClass()::query();
            }

            /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel> $query */
            $query = $resolveInfo->enhanceBuilder(
                $query,
                $this->directiveArgValue('scopes', []),
                $root,
                $args,
                $context,
                $resolveInfo
            );

            if ($this->paginationType()->isConnection()) {
                $first = $args['first'];
                $baseQuery = $this->getBaseQuery($query);
                $total = $baseQuery->getCountForPagination();

                /** @var \Lampager\Laravel\Paginator $query */
                $query = $query->lampager();

                $query = $query->useProcessor(CursorProcessor::class)
                    ->forward()
                    ->limit($first)
                    ->seekable()
                    ->exclusive();

                $hasIdOrder = false;
                if ($orders = $baseQuery->orders) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                        if ($order['column'] === 'id') {
                            $hasIdOrder = true;
                        }
                    }
                    $baseQuery->orders = [];
                }
                if (! $hasIdOrder) {
                    $query->orderBy('id');
                }

                $paginator = $query->paginate(Cursor::decode($args));
                $paginator->total = $total;

                return $paginator;
            }

            /** @phpstan-ignore-next-line */
            return PaginationArgs::extractArgs($args, $resolveInfo, $this->paginationType(), $this->paginateMaxCount())
                ->applyToBuilder($this->getBaseQuery($query));
        };
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function getResolverFromArgument(string $argumentName): \Closure
    {
        [$className, $methodName] = $this->getMethodArgumentParts($argumentName);

        $namespacesToTry = $argumentName === 'builder' ? config('lighthouse.namespaces.builders') : [];

        $namespacedClassName = $this->namespaceClassName($className, (array) $namespacesToTry);

        return Utils::constructResolver($namespacedClassName, $methodName);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation<TModel>|\Illuminate\Database\Query\Builder  $builder
     */
    protected function getBaseQuery($builder): Builder
    {
        if ($builder instanceof Relation) {
            return $builder->toBase();
        }
        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            return $builder->getQuery();
        }
        if ($builder instanceof Model) {
            return $builder->newModelQuery()->toBase();
        }

        return $builder;
    }

    protected function paginationType(): PaginationType
    {
        return new PaginationType(
            $this->directiveArgValue('type', PaginationType::CONNECTION)
        );
    }
}
