<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Illuminate\Support\Arr;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuwave\Lighthouse\GlobalId\GlobalIdDirective as BaseGlobalIdDirective;

class GlobalIdDirective extends BaseGlobalIdDirective
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Converts between IDs/types and global IDs.
When used upon a field, it encodes,
when used upon an argument, it decodes.
"""
directive @globalId(
  """
  By default, an array of `[$type, $id]` is returned when decoding.
  You may limit this to returning just one of both.
  Allowed values: "ARRAY", "TYPE", "ID"
  """
  decode: String = "ARRAY"
  """
  The type to be encoded with the ID. Defaults to the parent type of the field
  or the node model if specified.
  """
  type: String!
  """
  Throw an error if the global id is not valid
  """
  strict: Boolean = true
) on FIELD_DEFINITION | INPUT_FIELD_DEFINITION | ARGUMENT_DEFINITION
SDL;
    }

    /**
     * Resolve the field directive.
     */
    public function handleField(FieldValue $fieldValue): void
    {
        /** @var string $type */
        $type = $this->getType($fieldValue);

        $fieldValue->wrapResolver(
            fn ($resolver) => function () use ($type, $resolver): string {
                $resolvedValue = \call_user_func_array($resolver, \func_get_args());

                return $this->globalId->encode(
                    $type,
                    $resolvedValue
                );
            }
        );
    }

    /**
     * Decodes a global id given as an argument.
     *
     * @param  string|null  $argumentValue
     * @return string|string[]|null
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function sanitize(mixed $argumentValue): string|array|null
    {
        if ($argumentValue === null) {
            return null;
        }
        if ($decode = $this->directiveArgValue('decode')) {
            return match ($decode) {
                'TYPE' => $this->globalId->decodeType($argumentValue),
                'ID' => (function () use ($argumentValue) {
                    if (($type = $this->getType()) && $type !== $this->globalId->decodeType($argumentValue)) {
                        if ($this->directiveArgValue('strict', true)) {
                            $this->invalidId();
                        }

                        return null;
                    }

                    $id = $this->globalId->decodeID($argumentValue);
                    if (! is_numeric($id)) {
                        $this->invalidId();
                    }

                    return $id;
                })(),
                'ARRAY' => $this->globalId->decode($argumentValue),
                default => throw new DefinitionException("The argument of the @globalId directive can only be ID or TYPE, got {$decode}"),
            };
        }

        return $this->globalId->decode($argumentValue);
    }

    protected function getType(?FieldValue $fieldValue = null): ?string
    {
        if ($this->directiveHasArgument('type')) {
            return $this->directiveArgValue('type');
        }

        if (! $fieldValue) {
            return null;
        }

        $parent = $fieldValue->getParent()->getTypeDefinition();

        if ($parent instanceof ObjectTypeDefinitionNode) {
            /** @var \GraphQL\Language\AST\DirectiveNode|null $nodeDirective */
            $nodeDirective = Arr::first($parent->directives, fn (DirectiveNode $directive) => $directive->name->value === 'node');

            if ($nodeDirective) {
                /** @var \GraphQL\Language\AST\ArgumentNode|null $typeArgument */
                $typeArgument = Arr::first($nodeDirective->arguments, fn (ArgumentNode $argument) => $argument->name->value === 'type');

                if (! $typeArgument) {
                    $typeArgument = Arr::first($nodeDirective->arguments, fn (ArgumentNode $argument) => $argument->name->value === 'model');
                }

                if ($typeArgument) {
                    /** @var \GraphQL\Language\AST\StringValueNode $value */
                    $value = $typeArgument->value;

                    return $value->value;
                }
            }
        }

        return $fieldValue->getParentName();
    }

    protected function invalidId(): void
    {
        throw (new ModelNotFoundException)->setModel($this->getModelClass('type'));
    }
}
