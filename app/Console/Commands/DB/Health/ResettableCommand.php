<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health;

interface ResettableCommand
{
    public function reset(): int;
}
