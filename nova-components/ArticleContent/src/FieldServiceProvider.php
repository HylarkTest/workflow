<?php

declare(strict_types=1);

namespace Hylark\ArticleContent;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('article-content', __DIR__.'/../dist/js/field.js');
            Nova::style('article-content', __DIR__.'/../dist/css/field.css');
        });

        $this->publishes([
            __DIR__.'/../fonts/vendor' => public_path('images/vendor'),
        ], 'public');

        $this->routes();
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('nova:api')
            ->prefix('nova-vendor/hylark/article-content')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
