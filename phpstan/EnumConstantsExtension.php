<?php

declare(strict_types=1);

namespace App\PHPStan;

use BenSampo\Enum\Enum;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Rules\Constants\AlwaysUsedClassConstantsExtension;

class EnumConstantsExtension implements AlwaysUsedClassConstantsExtension
{
    public function isAlwaysUsed(ConstantReflection $constant): bool
    {
        $class = $constant->getDeclaringClass();
        $enumParent = $class->getAncestorWithClassName(Enum::class);

        return $enumParent !== null;
    }
}
