<?php

declare(strict_types=1);

namespace App\PHPStan;

use PHPStan\TrinaryLogic;
use Illuminate\Http\Request;
use Mappings\Models\Mapping;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use Illuminate\Database\Eloquent\Collection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

class RequestReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return ($classReflection->isSubclassOf(Request::class) || $classReflection->getName() === Request::class) &&
            $methodName === 'getMappingContext';
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): \PHPStan\Reflection\MethodReflection
    {
        return new class($classReflection) implements MethodReflection
        {
            protected ClassReflection $classReflection;

            public function __construct($classReflection)
            {
                $this->classReflection = $classReflection;
            }

            public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
            {
                return $this->classReflection;
            }

            public function isStatic(): bool
            {
                return false;
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
                return 'getMappingContext';
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
                        TypeCombinator::union(new ObjectType(Collection::class), new ObjectType(Mapping::class))
                    ),
                ];
            }

            public function isDeprecated(): \PHPStan\TrinaryLogic
            {
                return TrinaryLogic::createNo();
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
