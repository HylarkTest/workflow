<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use App\GraphQL\AppContext;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use LighthouseHelpers\Pagination\ConnectionField;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class ConnectionEdgeDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Assign the connection edge resolver function to a field.
"""
directive @connectionEdge on FIELD_DEFINITION
GRAPHQL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        /** @phpstan-ignore-next-line Not sure how to typehint the $args param */
        return function ($root, array $args, AppContext $context, ResolveInfo $resolveInfo) {
            return (new ConnectionField)->edgeResolver(
                $root,
                $args,
                $context,
                $resolveInfo
            );
        };
    }
}
