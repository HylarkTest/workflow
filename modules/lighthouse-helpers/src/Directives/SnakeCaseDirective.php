<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class SnakeCaseDirective extends BaseDirective implements ArgDirective, ArgTransformerDirective, FieldResolver
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Convert an argument from pascalCase (API standard) to snake_case (database standard).
"""
directive @snakeCase(
    """
    If the value is an object you can specify the keys of the object that
    should be converted (can only be used if modifyField is false).
    """
    key: String
) on ARGUMENT_DEFINITION | FIELD_DEFINITION
SDL;
    }

    /**
     * Apply transformations on the value of an argument given to a field.
     *
     * @param  mixed  $argumentValue
     * @return \Nuwave\Lighthouse\Execution\Arguments\ArgumentSet
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    public function transform($argumentValue): string|ArgumentSet
    {
        $this->validateDirective($argumentValue);
        if (\is_string($argumentValue)) {
            return Str::snake($argumentValue);
        }

        $keys = explode('.', $this->directiveArgValue('key'));

        return $this->mapThroughArg($argumentValue, $keys);
    }

    /**
     * Set a field resolver on the FieldValue.
     *
     * This must call $fieldValue->setResolver() before returning
     * the FieldValue.
     */
    public function resolveField(FieldValue $fieldValue): callable
    {
        return fn ($rootValue) => data_get($rootValue, Str::snake($fieldValue->getFieldName()));
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    protected function validateDirective(mixed $argumentValue): void
    {
        $key = $this->directiveArgValue('key', false);
        $error = null;

        $isArray = $argumentValue instanceof ArgumentSet;

        if (! $isArray && ! \is_string($argumentValue)) {
            $error = 'The argument value must be a string or an object containing a string';
        }
        if (! $isArray && $key) {
            $error = 'Cannot access the key of a scalar argument';
        }
        if ($isArray && ! $key) {
            $error = 'A key must be specified for object arguments';
        }

        if ($error) {
            throw new DirectiveException($error);
        }
    }

    /**
     * @param  string[]  $keys
     */
    protected function mapThroughArg(ArgumentSet $arg, array $keys): ArgumentSet
    {
        $key = array_shift($keys);

        Collection::make($arg->arguments)->each(function (Argument $value, string $index) use ($key, $keys, $arg) {
            $matchKey = $key === $index || $key === '*';

            if ($matchKey && \is_string($value->value) && ! \count($keys)) {
                $arg->addValue($index, Str::snake($value->value));
            }
            if ($matchKey && $value->value instanceof ArgumentSet && \count($keys)) {
                $this->mapThroughArg($value->value, $keys);
            }
        });

        return $arg;
    }
}
