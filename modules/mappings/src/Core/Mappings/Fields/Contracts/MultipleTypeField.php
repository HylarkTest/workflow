<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Contracts;

interface MultipleTypeField
{
    public static function possibleTypes(): array;

    public static function possibleInputTypes(): array;
}
