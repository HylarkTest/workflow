<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class StringArgsDirective implements FieldManipulator, FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add common arguments for string values
"""
directive @stringArgs on FIELD_DEFINITION
SDL;
    }

    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType
    ): void {
        $fieldDefinition->arguments = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->arguments,
            [
                Parser::inputValueDefinition('truncate: Int'),
                Parser::inputValueDefinition('suffix: String = "..."'),
            ],
        );
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(
            fn (callable $resolver) => function ($root, $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($resolver): string {
                $value = (string) $resolver($root, $args, $context, $resolveInfo);

                if (isset($args['truncate'])) {
                    $value = trim($value);
                    $originalLength = mb_strlen($value);

                    $value = mb_substr($value, 0, $args['truncate']);

                    if (mb_strlen($value) < $originalLength) {
                        $value .= $args['suffix'];
                    }
                }

                return $value;
            }
        );
    }
}
