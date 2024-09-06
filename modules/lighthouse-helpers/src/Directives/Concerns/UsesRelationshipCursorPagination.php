<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives\Concerns;

use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use LighthouseHelpers\Pagination\PaginationArgs;
use LighthouseHelpers\Pagination\PaginatedModelsLoader;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

/**
 * Trait DefaultPaginationMethods
 *
 * @property \GraphQL\Language\AST\FieldDefinitionNode $definitionNode
 */
trait UsesRelationshipCursorPagination
{
    use HasDefaultPaginationArguments;

    /**
     * Resolve the field directive.
     */
    public function resolveField(FieldValue $value): callable
    {
        /** @phpstan-ignore-next-line Not sure how to typehint the $args param */
        return function (Model $parent, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
            $relationName = $this->relation();

            $decorateBuilder = $this->makeBuilderDecorator($parent, $args, $context, $resolveInfo);
            $paginationArgs = $this->generatePaginationArgs($args, $resolveInfo);

            if (config('lighthouse.batchload_relations')) {
                /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $relationBatchLoader */
                $relationBatchLoader = BatchLoaderRegistry::instance(
                    $this->qualifyPath($args, $resolveInfo),
                    function () use ($relationName, $decorateBuilder, $paginationArgs): RelationBatchLoader {
                        if ($paginationArgs === null) {
                            $modelsLoader = new SimpleModelsLoader($relationName, $decorateBuilder);
                        } else {
                            $modelsLoader = new PaginatedModelsLoader($relationName, $decorateBuilder, $paginationArgs);
                        }

                        return new RelationBatchLoader($modelsLoader);
                    }
                );

                return $relationBatchLoader->load($parent);
            }

            /** @var \Illuminate\Database\Eloquent\Relations\Relation<\Illuminate\Database\Eloquent\Model> $relation */
            $relation = $parent->{$relationName}();

            $decorateBuilder($relation);

            return $paginationArgs !== null
                ? $paginationArgs->applyToBuilder($relation)
                : $relation->getResults();
        };
    }

    protected function generatePaginationArgs(array $args, ResolveInfo $resolveInfo): ?\Nuwave\Lighthouse\Pagination\PaginationArgs
    {
        $paginationType = $this->paginationType();
        if ($paginationType && $paginationType->isConnection()) {
            return PaginationArgs::extractArgs($args, $resolveInfo, $paginationType, $this->paginationMaxCount());
        }

        return $this->paginationArgs($args, $resolveInfo);
    }
}
