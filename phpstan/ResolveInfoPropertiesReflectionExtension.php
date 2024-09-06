<?php

declare(strict_types=1);

namespace App\PHPStan;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Reflection\ClassReflection;
use GraphQL\Type\Definition\ResolveInfo;
use PHPStan\Reflection\PropertyReflection;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use PHPStan\Reflection\PropertiesClassReflectionExtension;

class ResolveInfoPropertiesReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->getName() === ResolveInfo::class &&
            $propertyName === 'argumentSet';
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new class($classReflection) implements PropertyReflection
        {
            protected ClassReflection $declaringClass;

            public function __construct($declaringClass)
            {
                $this->declaringClass = $declaringClass;
            }

            public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
            {
                return $this->declaringClass;
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

            public function getReadableType(): \PHPStan\Type\Type
            {
                return new ObjectType(ArgumentSet::class);
            }

            public function getWritableType(): \PHPStan\Type\Type
            {
                return new ObjectType(ArgumentSet::class);
            }

            public function canChangeTypeAfterAssignment(): bool
            {
                return false;
            }

            public function isReadable(): bool
            {
                return true;
            }

            public function isWritable(): bool
            {
                return false;
            }

            public function isDeprecated(): \PHPStan\TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getDeprecatedDescription(): ?string
            {
                return null;
            }

            public function isInternal(): \PHPStan\TrinaryLogic
            {
                TrinaryLogic::createNo();
            }
        };
    }
}
