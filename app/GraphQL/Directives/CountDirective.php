<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\GraphQL\CountModelsLoader;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Schema\Directives\CountDirective as BaseCountDirective;

class CountDirective extends BaseCountDirective
{
    public function resolveField(FieldValue $fieldValue): callable
    {
        $resolveCb = parent::resolveField($fieldValue);

        $relation = $this->directiveArgValue('relation');
        if (\is_string($relation)) {
            return function (Model $parent, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
                /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $relationBatchLoader */
                $relationBatchLoader = BatchLoaderRegistry::instance(
                    array_merge(
                        $this->qualifyPath($args, $resolveInfo),
                        ['count']
                    ),
                    function () use ($parent, $args, $context, $resolveInfo): RelationBatchLoader {
                        return new RelationBatchLoader(
                            new CountModelsLoader($this->relation(), $this->makeBuilderDecorator(
                                $parent,
                                $args,
                                $context,
                                $resolveInfo,
                            ))
                        );
                    }
                );

                return $relationBatchLoader->load($parent);
            };
        }

        return $resolveCb;
    }
}
