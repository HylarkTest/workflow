<?php

declare(strict_types=1);

namespace Notes;

use MarkupUtils\Delta;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\PurifierServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use LighthouseHelpers\GraphQLServiceProvider;

class NotesServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $this->loadResources();

        Validator::extend('delta', static function (string $attribute, $value, array $parameters, \Illuminate\Validation\Validator $validator): bool {
            return \is_array($value)
                && isset($value['ops'])
                && \is_array($value['ops'])
                && ! collect($value['ops'])->some(fn ($part) => ! \is_array($part) || ! isset($part['insert']));
        });

        Validator::extend('delta_max', static function (string $attribute, $value, array $parameters, \Illuminate\Validation\Validator $validator): bool {
            $validator->requireParameterCount(1, $parameters, 'delta_max');

            $text = (string) (new Delta($value ?? ['ops' => []]))->convertToPlaintext();

            return $validator->validateMax($attribute, $text, $parameters);
        });
    }

    public function register(): void
    {
        $this->app->register(GraphQLServiceProvider::class);
        $this->app->register(PurifierServiceProvider::class);
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/notes.php' => config_path('notes.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/notes.php', 'notes');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }
}
