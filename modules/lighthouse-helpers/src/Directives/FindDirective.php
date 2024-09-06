<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use LighthouseHelpers\Directives\Concerns\UsesGlobalIdArg;

/**
 * Class FindDirective
 *
 * The Lighthouse @find directive tries to resolve the model name just based on
 * the name of the GraphQL type, however the class name can also be encoded in
 * the global ID which can be found using the @globalId directive.
 * This directive is based on the Lighthouse @find directive with some extra
 * logic for determining the class name.
 * This is useful when querying on an interface which can be implemented by
 * many different models.
 */
class FindDirective extends BaseDirective implements FieldResolver
{
    use UsesGlobalIdArg;

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Find a model based on the arguments provided.
"""
directive @find(
  """
  Specify the class name of the model to use.
  This is only needed when the default model resolution does not work.
  """
  model: String

  """
  Apply scopes to the underlying query.
  """
  scopes: [String!]
) on FIELD_DEFINITION
SDL;
    }

    /**
     * Resolve the field directive.
     */
    public function resolveField(FieldValue $fieldValue): callable
    {
        return function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ?Model {
            $results = $resolveInfo
                ->enhanceBuilder(
                    $this->globalIdBuilder($resolveInfo) ?: $this->getModelClass()::query(),
                    $this->directiveArgValue('scopes', []),
                    $root,
                    $args,
                    $context,
                    $resolveInfo,
                )
                ->get();

            if ($results->count() > 1) {
                throw new Error('The query returned more than one result.');
            }

            return $results->first();
        };
    }
}
