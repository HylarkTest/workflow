<?php

declare(strict_types=1);

namespace Markers\GraphQL\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use LighthouseHelpers\Directives\OrderableDirective;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class MarkerGroupsDirective extends BaseDirective implements FieldManipulator
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add the default marker group arguments and directives
"""
directive @markerGroups on FIELD_DEFINITION
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
            [Parser::inputValueDefinition('name: String @builder(method: "LighthouseHelpers\\\Builders\\\Filter@beginsWith")')],
        );

        $fieldDefinition->directives = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->directives,
            [Parser::constDirective('@orderable(table: "marker_groups")')],
        );

        $definitionNode = $this->definitionNode;
        /** @var \GraphQL\Language\AST\DirectiveNode $orderableDefinition */
        $orderableDefinition = ASTHelper::directiveDefinition($fieldDefinition, 'orderable');
        /** @var \LighthouseHelpers\Directives\OrderableDirective $orderableDirective */
        $orderableDirective = (new OrderableDirective)->hydrate($orderableDefinition, $definitionNode);
        $orderableDirective->manipulateFieldDefinition($documentAST, $fieldDefinition, $parentType);
    }
}
