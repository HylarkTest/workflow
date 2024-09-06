<?php

declare(strict_types=1);

namespace AccountIntegrations;

use Google\Client;
use AccountIntegrations\Core\Scope;
use Illuminate\Support\Facades\Route;
use AccountIntegrations\Core\Provider;
use Illuminate\Support\ServiceProvider;
use LighthouseHelpers\Core\NativeEnumType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Events\Dispatcher;
use AccountIntegrations\Core\ConfigRetriever;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use AccountIntegrations\Core\MicrosoftGraphGateway;
use SocialiteProviders\Google\GoogleExtendSocialite;
use AccountIntegrations\Http\Controllers\GoogleCallbackController;
use AccountIntegrations\Http\Controllers\GoogleRedirectController;
use AccountIntegrations\Http\Controllers\EmailAttachmentController;
use AccountIntegrations\Core\Todos\Repositories\GoogleTodoRepository;
use AccountIntegrations\Http\Controllers\MicrosoftCallbackController;
use AccountIntegrations\Http\Controllers\MicrosoftRedirectController;
use AccountIntegrations\Core\Emails\Repositories\GoogleEmailRepository;
use AccountIntegrations\Core\Todos\Repositories\MicrosoftTodoRepository;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use AccountIntegrations\Core\Calendar\Repositories\GoogleCalendarRepository;
use AccountIntegrations\Core\Calendar\Repositories\MicrosoftCalendarRepository;
use AccountIntegrations\Core\Emails\Repositories\MicrosoftGraphEmailRepository;

class AccountIntegrationsServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        MicrosoftGraphEmailRepository::class,
        GoogleEmailRepository::class,
        MicrosoftCalendarRepository::class,
        GoogleCalendarRepository::class,
        MicrosoftTodoRepository::class,
        GoogleTodoRepository::class,
        MicrosoftGraphGateway::class,
    ];

    public function boot(Dispatcher $events, TypeRegistry $typeRegistry): void
    {
        $this->publishes([
            __DIR__.'/../config/account-integrations.php' => config_path('account-integrations.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/account-integrations.php', 'account-integrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        //        $events->listen(SocialiteWasCalled::class, [AppleExtendSocialite::class, 'handle']);
        $events->listen(SocialiteWasCalled::class, [GoogleExtendSocialite::class, 'handle']);
        $events->listen(SocialiteWasCalled::class, [AzureExtendSocialite::class, 'handle']);

        Route::middleware(config('account-integrations.middleware'))->group(function () {
            Route::get('integrate/google', GoogleRedirectController::class)->name('integrations.google.redirect');
            Route::get('callback/google', GoogleCallbackController::class)->name('integrations.google.callback');
            Route::get('integrate/microsoft', MicrosoftRedirectController::class)->name('integrations.microsoft.redirect');
            Route::get('callback/microsoft', MicrosoftCallbackController::class)->name('integrations.microsoft.callback');
        });

        Route::middleware(config('account-integrations.link-middleware'))->group(function () {
            Route::get('attachment/{accountId}/mailbox/{mailboxId}/email/{emailId}/attachment/{attachmentId}', [EmailAttachmentController::class, 'show'])
                ->name('email-attachment-download-link');
        });

        $typeRegistry->register(new NativeEnumType(Provider::class));
        $typeRegistry->register(new NativeEnumType(Scope::class));

        $modelNamespaces = $this->app->make('config')->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'AccountIntegrations\\Models';
        $this->app->make('config')->set(['lighthouse.namespaces.models' => $modelNamespaces]);
    }

    public function register()
    {
        $this->app->singleton(ConfigRetrieverInterface::class, function () {
            return new ConfigRetriever;
        });

        $this->app->bind(Client::class, function () {
            return new Client([
                'application_name' => config('app.name'),
                'client_id' => config('account-integrations.google.client_id'),
                'client_secret' => config('account-integrations.google.client_secret'),
                'redirect_uri' => config('account-integrations.google.redirect'),
                // 'cache' => app('cache.psr6'),
            ]);
        });

        foreach ($this->repositories as $repository) {
            $this->app->bind($repository, function ($app, $parameters) use ($repository) {
                return new $repository(...$parameters);
            });
        }
    }
}
