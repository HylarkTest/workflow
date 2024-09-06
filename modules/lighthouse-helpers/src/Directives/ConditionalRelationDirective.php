<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use GraphQL\Deferred;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use LighthouseHelpers\Directives\Concerns\UsesRelationshipCursorPagination;

class ConditionalRelationDirective extends RelationDirective implements FieldManipulator
{
    use UsesRelationshipCursorPagination {
        resolveField as traitResolveField;
        directiveArgValue as traitDirectiveArgValue;
    }

    protected bool $useTrueRelation = false;

    /**
     * SDL definition of the directive.
     */
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Resolves a field through a relationship based on the arguments passed in
"""
directive @conditionalRelation(
  """
  Specify the argument name that contains the condition that specifies which
  relation to use.
  """
  arg: String!

  """
  Specify the relationship method name in the model class,
  if it is named different from the field in the schema.
  """
  relation: String

  """
  The relation to call if the argument field is true.
  """
  trueRelation: String!

  """
  Apply scopes to the underlying query.
  """
  scopes: [String!]

  """
  ALlows to resolve the relation as a paginated list.
  Allowed values: paginator, connection.
  """
  type: PaginateType = PAGINATOR

  """
  Specify the default quantity of elements to be returned.
  Only applies when using pagination.
  """
  defaultCount: Int

  """
  Specify the maximum quantity of elements to be returned.
  Only applies when using pagination.
  """
  maxCount: Int

  """
  Specify a custom type that implements the Edge interface
  to extend edge object.
  Only applies when using Relay style "connection" pagination.
  """
  edgeType: String
) on FIELD_DEFINITION
SDL;
    }

    /**
     * Resolve the field directive.
     */
    public function resolveField(FieldValue $value): callable
    {
        $originalResolver = $this->traitResolveField($value);

        return function (Model $parent, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($originalResolver): Deferred {
            $argKey = $this->directiveArgValue('arg');
            $condition = $args[$argKey] ?? false;
            $this->useTrueRelation = $condition;
            if ($condition) {
                $resolveInfo->path[] = $argKey;
            }

            return $originalResolver($parent, $args, $context, $resolveInfo);
        };
    }

    /**
     * @param  mixed|null  $default
     * @return mixed|null
     */
    protected function directiveArgValue(string $name, $default = null): mixed
    {
        if ($name === 'relation' && $this->useTrueRelation) {
            $name = 'trueRelation';
            $default = null;
        }

        return $this->traitDirectiveArgValue($name, $default);
    }
}
