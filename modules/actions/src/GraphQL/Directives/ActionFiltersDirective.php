<?php

declare(strict_types=1);

namespace Actions\GraphQL\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class ActionFiltersDirective implements FieldManipulator
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add filter arguments for createdBy and lastUpdatedBy on a type that has
actions.
The global ID will be used to determine which types to filter.
"""
directive @actionFilters on FIELD_DEFINITION
SDL;
    }

    /**
     * Manipulate the AST based on a field definition.
     */
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType
    ): void {
        $fieldDefinition->arguments = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->arguments,
            [
                Parser::inputValueDefinition('createdBy: [ID!] @builder(method: "Actions\\\GraphQL\\\Builders\\\ActionsFilter@createdBy")'),
                Parser::inputValueDefinition('lastUpdatedBy: [ID!] @builder(method: "Actions\\\GraphQL\\\Builders\\\ActionsFilter@lastUpdatedBy")'),
            ],
        );
    }
}
