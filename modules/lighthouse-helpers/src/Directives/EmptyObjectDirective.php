<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use App\GraphQL\AppContext;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;

class EmptyObjectDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
If a JSON scalar is empty this will turn it into an empty object instead of an
array.
"""
directive @emptyObject on FIELD_DEFINITION
SDL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        /** @phpstan-ignore-next-line Not sure how to typehint the $args param */
        $fieldValue->wrapResolver(fn (callable $originalResolver) => function ($root, array $args, AppContext $context, ResolveInfo $resolveInfo) use ($originalResolver) {
            $value = $originalResolver($root, $args, $context, $resolveInfo);
            if (\is_array($value) && empty($value)) {
                return new \stdClass;
            }

            return $value;
        });
    }
}
