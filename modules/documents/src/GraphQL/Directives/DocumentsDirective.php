<?php

declare(strict_types=1);

namespace Documents\GraphQL\Directives;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use LighthouseHelpers\Directives\OrderableDirective;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class DocumentsDirective extends BaseDirective implements FieldManipulator
{
    protected DirectiveFactory $factory;

    /**
     * MappingsDirective constructor.
     */
    public function __construct(DirectiveFactory $factory)
    {
        $this->factory = $factory;
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add the default tag group arguments and directives
"""
directive @documents on FIELD_DEFINITION
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
            [Parser::inputValueDefinition('filename: String @builder(method: "LighthouseHelpers\\\Builders\\\Filter@beginsWith")')],
        );

        $fieldDefinition->directives = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->directives,
            [Parser::constDirective('@orderable(table: "documents")')],
        );

        /** @var \GraphQL\Language\AST\DirectiveNode $orderableDefinition */
        $orderableDefinition = ASTHelper::directiveDefinition($fieldDefinition, 'orderable');
        /** @phpstan-var \LighthouseHelpers\Directives\OrderableDirective $orderableDirective */
        $orderableDirective = (new OrderableDirective)->hydrate($orderableDefinition, $fieldDefinition);
        $orderableDirective->manipulateFieldDefinition($documentAST, $fieldDefinition, $parentType);
    }
}
