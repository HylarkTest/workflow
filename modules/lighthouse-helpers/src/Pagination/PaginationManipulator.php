<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Pagination\PaginationType;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Pagination\PaginationManipulator as BasePaginationManipulator;

class PaginationManipulator extends BasePaginationManipulator
{
    /**
     * Register connection with schema.
     */
    protected function registerConnection(
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
        PaginationType $paginationType,
        ?int $defaultCount = null,
        ?int $maxCount = null,
        ?ObjectTypeDefinitionNode $edgeType = null
    ): void {
        parent::registerConnection($fieldDefinition, $parentType, $paginationType, $defaultCount, $maxCount, $edgeType);

        /*
         * All we want to do in this method is replace the implementation of
         * ConnectionField with our custom one that deals with cursors properly.
         * So rather than re-implement the entire method for such a small change
         * we can instead run the parent method then find the parsed directive
         * and replace the argument within the AST.
         */
        $fieldTypeName = ASTHelper::getUnderlyingTypeName($fieldDefinition);

        $originalConnectionFieldName = \Nuwave\Lighthouse\Pagination\ConnectionField::class;
        $connectionFieldName = ConnectionField::class;

        /*
         * So in this case $fieldTypeName is the Connection name for the
         * specific type e.g. ItemConnection which has two fields:
         *   PageInfo
         *   Edges
         * Both of these make use of the @field directive which points to the
         * ConnectionField class to resolve the fields.
         * So we loop through the two fields. Fetch the first directive (there
         * is only one - @field), fetch the first argument from that directive
         * (there is only one - resolver) and replace the value of that argument
         * with our custom class.
         *
         * The implementation of this method is quite likely to change but the
         * definition of the field created at the end is not. So it makes more
         * sense to modify the method this way to avoid conflicts in the future.
         */
        foreach ($this->documentAST->types[$fieldTypeName]->fields as $type) {
            $directiveArgument = $type->directives[0]->arguments[0]->value ?? null;

            if ($directiveArgument) {
                $directiveArgument->value = str_replace($originalConnectionFieldName, $connectionFieldName, $directiveArgument->value);
            }
        }
    }

    protected function pageInfo(): ObjectTypeDefinitionNode
    {
        return Parser::objectTypeDefinition(/** @lang GraphQL */ '
            "Information about pagination using a Relay style cursor connection."
            type PageInfo {
              "When paginating forwards, are there more items?"
              hasNextPage: Boolean!

              "When paginating backwards, are there more items?"
              hasPreviousPage: Boolean!

              "The cursor to continue paginating backwards."
              startCursor: String

              "The cursor to continue paginating forwards."
              endCursor: String

              "Total number of nodes in the paginated connection."
              total: Int!

              "Total number of nodes without any filters."
              rawTotal: Int

              "Number of nodes in the current page."
              count: Int!

              "Index of the current page."
              currentPage: Int!

              "Index of the last available page."
              lastPage: Int!
            }
        ');
    }
}
