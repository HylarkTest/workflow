<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Schema\Values\FieldValue;
use LighthouseHelpers\Pagination\ConnectionField;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class PageInfoDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Assign the connection edge resolver function to a field.
"""
directive @pageInfo on FIELD_DEFINITION
GRAPHQL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        return function ($root) {
            return (new ConnectionField)->pageInfoResolver($root);
        };
    }
}
