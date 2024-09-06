<?php

declare(strict_types=1);

namespace App\PHPStan;

use PHPStan\TrinaryLogic;
use Actions\Core\ActionType;
use PHPStan\Type\StaticType;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Reflection\MethodsClassReflectionExtension;

class ActionTypeReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->getName() === ActionType::class) {
            return false;
        }

        return ActionType::hasKey($methodName);
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): \PHPStan\Reflection\MethodReflection
    {
        return new class($classReflection, $methodName) implements MethodReflection
        {
            protected ClassReflection $classReflection;

            protected string $methodName;

            public function __construct(ClassReflection $classReflection, string $methodName)
            {
                $this->classReflection = $classReflection;
                $this->methodName = $methodName;
            }

            public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
            {
                return $this->classReflection;
            }

            public function isStatic(): bool
            {
                return true;
            }

            public function isPrivate(): bool
            {
                return false;
            }

            public function isPublic(): bool
            {
                return true;
            }

            public function getDocComment(): ?string
            {
                return null;
            }

            public function getName(): string
            {
                return $this->methodName;
            }

            public function getPrototype(): \PHPStan\Reflection\ClassMemberReflection
            {
                return $this;
            }

            public function getVariants(): array
            {
                return [
                    new FunctionVariant(
                        TemplateTypeMap::createEmpty(),
                        null, [], false,
                        new StaticType(ActionType::class)
                    ),
                ];
            }

            public function isDeprecated(): \PHPStan\TrinaryLogic
            {
                return null;
            }

            public function getDeprecatedDescription(): ?string
            {
                return null;
            }

            public function isFinal(): \PHPStan\TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function isInternal(): \PHPStan\TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getThrowType(): ?\PHPStan\Type\Type
            {
                return null;
            }

            public function hasSideEffects(): \PHPStan\TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }
        };
    }
}
