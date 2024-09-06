<?php

declare(strict_types=1);

namespace KeyValueStore;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use KeyValueStore\Commands\StoreCommand;

class KeyValueStoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/key-value-store.php.php' => config_path('key-value-store.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/key-value-store.php', 'key-value-store');

        $this->configureRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                StoreCommand::class,
            ]);
        }
    }

    protected function configureRoutes(): void
    {
        Route::group([
            'domain' => config('key-value-store.domain', null),
            'prefix' => config('key-value-store.prefix'),
            'middleware' => config('key-value-store.middleware'),
        ], function () {
            Route::get('/{key}', [KeyValueStoreController::class, 'show'])->name('store.show');
            Route::get('/', [KeyValueStoreController::class, 'index'])->name('store.index');
            Route::post('/{key}', [KeyValueStoreController::class, 'store'])->name('store.store');
            Route::delete('/{key}', [KeyValueStoreController::class, 'destroy'])->name('store.destroy');
        });
    }
}
