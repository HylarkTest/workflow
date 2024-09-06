<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class RootDirective implements Directive, FieldResolver
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Resolve the root of the field
"""
directive @root on FIELD_DEFINITION | OBJECT
SDL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        return fn ($root) => $root ?: new \stdClass;
    }
}
