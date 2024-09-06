<?php

declare(strict_types=1);

namespace App\Console\Concerns;

trait TimesActions
{
    /**
     * Perform the action and output the time it took.
     */
    protected function timeAction(string $action, \Closure $callback): void
    {
        $this->info($action);

        $start = microtime(true);

        $callback();

        $runTime = round(microtime(true) - $start, 2);

        $this->line("Completed ($runTime seconds)");
    }
}
