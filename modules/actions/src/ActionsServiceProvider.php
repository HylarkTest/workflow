<?php

declare(strict_types=1);

namespace Actions;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Actions\Core\ActionRecorder;
use Actions\Core\ActionTranslator;
use Actions\Core\ActionEventManager;
use BenSampo\Enum\EnumServiceProvider;
use Actions\Commands\LatestSyncCommand;
use Actions\Models\Concerns\HasActions;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use LighthouseHelpers\GraphQLServiceProvider;
use LighthouseHelpers\Core\DynamicLaravelEnumType;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Actions\Core\Contracts\ActionRecorder as ActionRecorderInterface;
use Actions\Core\Contracts\ActionTranslator as ActionTranslatorInterface;
use Actions\Core\Contracts\ActionEventManager as ActionEventManagerInterface;

class ActionsServiceProvider extends ServiceProvider
{
    public function boot(
        TypeRegistry $registry,
        ActionEventManagerInterface $eventManager,
        ActionRecorderInterface $recorder,
        ActionTranslatorInterface $translator,
    ): void {
        $this->loadResources();

        $this->bootGraphQL($registry);

        $this->addListenersToWatchedModels($eventManager);

        Action::setActionTranslator($translator);

        $recorder->setUserResolver(function () {
            return $this->app->make('auth')->user();
        });
    }

    public function register(): void
    {
        $this->app->register(GraphQLServiceProvider::class);
        $this->app->register(EnumServiceProvider::class);
        $this->app->bind(ActionEventManagerInterface::class, ActionEventManager::class);
        $this->app->bind(ActionTranslatorInterface::class, ActionTranslator::class);
        $this->app->singleton(ActionRecorderInterface::class, ActionRecorder::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                LatestSyncCommand::class,
            ]);
        }
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/actions.php' => config_path('actions.php'),
            __DIR__.'/../lang' => resource_path('lang/vendor'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/actions.php', 'actions');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'actions');
    }

    protected function bootGraphQL(TypeRegistry $registry): void
    {
        $modelNamespaces = $this->app->make('config')->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'Actions\\Models';
        $this->app->make('config')->set(['lighthouse.namespaces.models' => $modelNamespaces]);

        /*
         * We register the ActionType enum after all service providers have
         * booted in order to give them a chance to extend the enum to
         * use custom types.
         */
        $this->app->booted(fn () => $registry->overwrite(new DynamicLaravelEnumType(ActionType::class)));

        $this->app->make('events')->listen(
            RegisterDirectiveNamespaces::class,
            fn () => 'Actions\\GraphQL\\Directives'
        );
    }

    protected function addListenersToWatchedModels(ActionEventManagerInterface $eventManager): void
    {
        foreach ($this->app->make('config')->get('actions.watch') as $model) {
            /*
             * If the model uses the HasActions trait we shouldn't listen
             * for changes here.
             */
            if (
                $this->app->make('config')->get('actions.automatic')
                && \in_array(HasActions::class, class_uses($model) ?: [], true)
            ) {
                return;
            }

            $eventManager->listenToModelEvents($model);
        }
    }
}
