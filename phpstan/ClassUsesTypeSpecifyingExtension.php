<?php

declare(strict_types=1);

namespace App\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\SpecifiedTypes;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Type\FunctionTypeSpecifyingExtension;

class ClassUsesTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    protected TypeSpecifier $typeSpecifier;

    public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
    {
        return false;
        //        return $functionReflection->getName() === 'in_array' &&
        //            isset($node->args[0], $node->args[1]) &&
        //            $node->args[0]->value instanceof ClassConstFetch &&
        //            $node->args[1]->value instanceof FuncCall &&
        //            $node->args[1]->value->name->parts[0] === 'class_uses_recursive' &&
        //            !$context->null();
    }

    public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
    {
        $param = $node->args[1]->value->args[0]->value;
        $trait = $node->args[0]->value;

        return $this->typeSpecifier->create(
            $param,
            TypeCombinator::intersect($scope->getType($param), new ObjectType($scope->getType($trait)->getValue())),
            $context
        );
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
