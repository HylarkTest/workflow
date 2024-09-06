<?php

declare(strict_types=1);

namespace App\Core\Imports\Importers;

use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class GenericImport implements SkipsEmptyRows, WithLimit
{
    use Importable;

    public function __construct(protected int $limit = 6) {}

    public function limit(): int
    {
        return $this->limit;
    }
}
