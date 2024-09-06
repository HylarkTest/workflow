<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nuwave\Lighthouse\Execution\Arguments\SaveModel;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Schema\Directives\OneModelMutationDirective;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class CreateOnRelationDirective extends OneModelMutationDirective
{
    /**
     * @var \Illuminate\Database\Eloquent\Relations\Relation<TModel>
     */
    protected Relation $relation;

    public function name(): string
    {
        return 'createOnRelation';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Create a new Eloquent model with the given arguments on a relationship of the
parent node or from context.
"""
directive @createOnRelation(
  """
  Specify the class name of the model to use.
  This is only needed when the default model resolution does not work.
  """
  model: String
  """
  Specify the name of the relation on which the model should be created.
  """
  relation: String!
  """
  A path to the model in the context that should be fetched to attach the
  relationship to e.g. "user". If not specified the model will be created on the
  parent.
  """
  context: String
) on FIELD_DEFINITION
SDL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        $originalResolver = parent::resolveField($fieldValue);

        return function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($originalResolver) {
            if ($path = $this->directiveArgValue('context')) {
                $root = data_get($context, $path);
            }

            $relationName = $this->directiveArgValue('relation');

            $this->relation = $root->$relationName();

            return $originalResolver($root, $args, $context, $resolveInfo);
        };
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Relations\Relation<TModel>|null  $parentRelation
     */
    protected function makeExecutionFunction(?Relation $parentRelation = null): callable
    {
        return new SaveModel($this->relation);
    }
}
