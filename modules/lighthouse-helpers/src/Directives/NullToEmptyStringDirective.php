<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;

class NullToEmptyStringDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Change null to an empty string
"""
directive @nullToEmptyString on FIELD_DEFINITION
SDL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->resultHandler(
            fn ($result) => $result ?? ''
        );
    }
}
