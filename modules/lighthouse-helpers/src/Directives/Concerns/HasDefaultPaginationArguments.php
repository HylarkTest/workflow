<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives\Concerns;

use PHPStan\ShouldNotHappenException;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use LighthouseHelpers\Pagination\PaginationManipulator;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;

/**
 * Trait DefaultPaginationMethods
 *
 * @mixin \Nuwave\Lighthouse\Pagination\PaginateDirective
 */
trait HasDefaultPaginationArguments
{
    /**
     * Overriding the parent method in order to implement our own
     * PaginationManipulator that will allow us to do cursor pagination
     * properly.
     */
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType): void
    {
        // We default to not changing the field if no pagination type is set explicitly.
        // This makes sense for relations, as there should not be too many entries.
        if (! $this->directiveHasArgument('type') && ! is_a($this, PaginateDirective::class)) {
            return;
        }

        $paginationManipulator = new PaginationManipulator($documentAST);

        if ($this->directiveHasArgument('builder')) {
            // This is done only for validation
            $this->getResolverFromArgument('builder');
        } else {
            $paginationManipulator->setModelClass(
                $this->getModelClass()
            );
        }

        $maxCount = $this instanceof RelationDirective ? $this->paginationMaxCount() : $this->paginateMaxCount();
        $defaultCount = $this instanceof RelationDirective ? $this->paginationDefaultCount() : $this->defaultCount();

        $type = $this->paginationType();

        /** @phpstan-ignore-next-line Some calls to pagination type always return a value, some return null. PHPStan can't tell the difference */
        throw_if(! $type, ShouldNotHappenException::class, 'The type is validated by GraphQL');

        $paginationManipulator->transformToPaginatedField(
            $type,
            $fieldDefinition,
            $parentType,
            $defaultCount,
            $maxCount,
        );
    }

    /**
     * @param  mixed|null  $default
     * @return mixed|null
     */
    protected function directiveArgValue(string $name, $default = null): mixed
    {
        if ($name === 'defaultCount' && ! $default) {
            $default = 20;
        }

        return parent::directiveArgValue($name, $default);
    }
}
