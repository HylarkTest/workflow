<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class Base64Directive extends BaseDirective implements ArgDirective, ArgTransformerDirective, FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Convert an argument or field to base64
"""
directive @base64 on ARGUMENT_DEFINITION | FIELD_DEFINITION
SDL;
    }

    public function transform($argumentValue): string
    {
        $value = base64_decode($argumentValue, true);
        throw_if($value === false, new \InvalidArgumentException('Invalid base64 string'));

        return $value;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->resultHandler(
            fn ($result): ?string => $result === null
                ? null
                : base64_encode($result)
        );
    }
}
