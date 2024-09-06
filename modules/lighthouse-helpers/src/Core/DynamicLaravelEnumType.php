<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use Nuwave\Lighthouse\Schema\Types\LaravelEnumType;

/**
 * It is possible that someone might want to extend the Laravel Enum type beyond
 * the default behaviour of using the private constants (they are private after
 * all). Lighthouse currently uses reflection to look at the private properties
 * to check if there is a deprecation notice which breaks if the property does
 * not exist. This class brings back the original behaviour for those who don't
 * care about deprecation and want to use extendable enums.
 *
 * @extends \Nuwave\Lighthouse\Schema\Types\LaravelEnumType<string, \BenSampo\Enum\Enum<string>>
 */
class DynamicLaravelEnumType extends LaravelEnumType
{
    protected function deprecationReason(string $key): ?string
    {
        return null;
    }
}
