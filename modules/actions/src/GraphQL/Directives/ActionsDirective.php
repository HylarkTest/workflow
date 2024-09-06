<?php

declare(strict_types=1);

namespace Actions\GraphQL\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class ActionsDirective extends BaseDirective implements FieldManipulator
{
    //    /**
    //     * @var \Nuwave\Lighthouse\Schema\Factories\DirectiveFactory
    //     */
    //    protected DirectiveFactory $factory;
    //
    //    /**
    //     * MappingsDirective constructor.
    //     */
    //    public function __construct(DirectiveFactory $factory)
    //    {
    //        $this->factory = $factory;
    //    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add the default action arguments and directives
"""
directive @actions on FIELD_DEFINITION
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
                Parser::inputValueDefinition('type: [ActionType!] @in'),
                Parser::inputValueDefinition('notType: [ActionType!] @in'),
                Parser::inputValueDefinition('performer: [ID!] @builder(method: "Actions\\\GraphQL\\\Builders\\\ActionsFilter@performedBy")'),
            ],
        );

        $fieldDefinition->directives = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->directives,
            [Parser::constDirective('@orderable(table: "actions")')],
        );

        //        $definitionNode = $this->definitionNode;
        //        $orderableDefinition = ASTHelper::directiveDefinition($fieldDefinition, 'orderable');
        //        /** @var \LighthouseHelpers\Directives\OrderableDirective $orderableDirective */
        //        $orderableDirective = $this->factory->create('orderable');
        //        $orderableDirective->hydrate($orderableDefinition, $definitionNode);
        //        $orderableDirective->manipulateFieldDefinition($documentAST, $fieldDefinition, $parentType);
    }
}
