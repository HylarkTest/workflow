<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use LighthouseHelpers\Utils;
use LighthouseHelpers\Core\ModelBatchLoader;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class BatchLoadDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Batch load models based on their global id
"""
directive @batchLoad(
    """
    The attribute on the root object that contains the global id that should be
    used to load the models.
    """
    attribute: String
    """
    The method on the root object that returns the global id that should be
    used to load the models.
    """
    method: String
    """
    The model class that should be used to find the model. If this is provided
    the attribute will be treated as a normal id not a global ID.
    """
    type: String
) on FIELD_DEFINITION
SDL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        return function ($root) {
            $id = $this->getIdFromRoot($root);

            if (! $id) {
                return null;
            }

            if ($type = $this->directiveArgValue('type')) {
                $model = Utils::namespaceModelClass($type);

                if (! $model) {
                    throw new \RuntimeException("Classname couldn't be found for [$type]");
                }

                return ModelBatchLoader::instanceFromModel($model)
                    ->loadAndResolve($id);
            }

            /** @var string $id */
            return ModelBatchLoader::instanceFromGlobalId($id);
        };
    }

    /**
     * @param  mixed  $root
     */
    protected function getIdFromRoot($root): string|int|null
    {
        if ($this->directiveHasArgument('attribute')) {
            return data_get($root, $this->directiveArgValue('attribute'));
        }
        if ($this->directiveHasArgument('method')) {
            $method = $this->directiveArgValue('method');

            return (string) $root->$method();
        }
        /** @var \GraphQL\Language\AST\ObjectFieldNode $node */
        $node = $this->definitionNode;

        return $root->{$node->name->value};
    }
}
