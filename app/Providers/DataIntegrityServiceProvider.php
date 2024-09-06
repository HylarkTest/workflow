<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class DataIntegrityServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        // Loop through classes in `DataIntegrity` directory
        foreach ((array) glob(app_path('DataIntegrity/*.php')) as $file) {
            /** @phpstan-ignore-next-line This will not fail */
            $class = 'App\\DataIntegrity\\'.pathinfo($file, PATHINFO_FILENAME);
            /** @var \App\DataIntegrity\DataIntegrity $integrity */
            $integrity = new $class;
            foreach ($integrity->getEvents() as $event => $method) {
                $events->listen($event, [$integrity, $method]);
            }
        }
    }
}
