<?php

declare(strict_types=1);

namespace App\Core\Imports\Importers;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class RowCounter implements WithEvents
{
    use RegistersEventListeners;

    protected int $count = 0;

    public function __construct(protected bool $firstRowIsHeader) {}

    public function getCount(): int
    {
        return $this->count - ($this->firstRowIsHeader ? 1 : 0);
    }

    public function beforeImport(BeforeImport $event): void
    {
        $this->count = array_sum($event->reader->getTotalRows());
    }
}
