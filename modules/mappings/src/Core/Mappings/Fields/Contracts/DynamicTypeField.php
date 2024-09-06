<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Contracts;

interface DynamicTypeField
{
    public function registerDynamicFields(string $prefix): void;
}
