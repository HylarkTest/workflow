<?php

declare(strict_types=1);

namespace App\Console\Concerns;

use App\Models\CronResult;

trait StoresCronResults
{
    protected function storeCronResult(string $column, int $value): void
    {
        CronResult::query()->create([
            $column => $value,
        ]);
    }
}
