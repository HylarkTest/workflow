<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class OrderableDirective extends BaseDirective implements FieldManipulator
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add an orderBy argument transforming the field parameter to snake case.
"""
directive @orderable(
  """
  Specify the table that should be use to qualify the column name.
  """
  table: String
) on FIELD_DEFINITION
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
        $table = $this->directiveArgValue('table');
        $orderByArgs = $table ? "(table: \"$table\")" : '';
        $fieldDefinition->arguments = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->arguments,
            [Parser::inputValueDefinition("orderBy: [OrderByClause!] @safeOrderBy$orderByArgs @snakeCase(key: \"column\")")]
        );
    }
}
