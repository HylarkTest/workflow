<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Contracts;

interface CustomSortable
{
    public function toSortable(mixed $data): mixed;
}
