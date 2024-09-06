<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use App\GraphQL\AppContext;
use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class HtmlArgsDirective implements FieldManipulator, FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add common arguments for string values
"""
directive @htmlArgs on FIELD_DEFINITION
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
                Parser::inputValueDefinition('stripTags: Boolean'),
            ],
        );
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(
            /** @phpstan-ignore-next-line Not sure how to typehint the $args param */
            fn ($resolver) => function ($root, $args, AppContext $context, ResolveInfo $resolveInfo) use ($resolver): string {
                $value = $resolver($root, $args, $context, $resolveInfo);

                if (isset($args['truncate'])) {
                    $originalLength = mb_strlen($value);

                    $value = trim(mb_substr($value, 0, $args['truncate']));

                    if (mb_strlen($value) < $originalLength) {
                        $value .= $args['suffix'];
                    }
                }

                if ($args['stripTags'] ?? false) {
                    $value = strip_tags($value);
                }

                return $value;
            }
        );
    }
}
