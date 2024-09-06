<?php

declare(strict_types=1);

namespace CitusLaravel;

use Illuminate\Support\ServiceProvider;
use CitusLaravel\Commands\CitusDistributeCommand;

class CitusLaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            CitusDistributeCommand::class,
        ]);
    }
}
