<?php

declare(strict_types=1);

namespace App\PHPStan;

use Color\Color;
use PHPStan\Analyser\Scope;
use PHPStan\Type\StringType;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;

class ColorTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    protected TypeSpecifier $typeSpecifier;

    public function getClass(): string
    {
        return Color::class;
    }

    public function isStaticMethodSupported(MethodReflection $staticMethodReflection, StaticCall $node, TypeSpecifierContext $context): bool
    {
        return in_array($staticMethodReflection->getName(), ['isHex', 'isHsl', 'isRgb']) && ($context->true());
    }

    public function specifyTypes(MethodReflection $staticMethodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context): \PHPStan\Analyser\SpecifiedTypes
    {
        return $this->typeSpecifier->create($node->args[0]->value, new StringType, $context);
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
