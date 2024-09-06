<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use App\Core\EloquentUserProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use App\Models\Support\SupportFolder;
use App\Models\Support\SupportArticle;
use App\Policies\SupportArticlePolicy;
use App\Models\Support\SupportCategory;
use App\Http\Responses\Auth\LoginResponse;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Http\Responses\Auth\PasswordResetResponse;
use Illuminate\Notifications\Messages\MailMessage;
use App\Http\Responses\Auth\PasswordUpdateResponse;
use App\Http\Responses\Auth\PasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SupportArticle::class => SupportArticlePolicy::class,
        SupportCategory::class => SupportArticlePolicy::class,
        SupportFolder::class => SupportArticlePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->app->bind(FailedPasswordResetLinkRequestResponse::class, PasswordResetLinkRequestResponse::class);
        $this->app->bind(SuccessfulPasswordResetLinkRequestResponse::class, PasswordResetLinkRequestResponse::class);
        $this->app->bind(PasswordUpdateResponseContract::class, PasswordUpdateResponse::class);
        $this->app->bind(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);

        $this->app->make('auth')->provider('eloquent', function ($app, array $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });

        Fortify::authenticateUsing(function (Request $request) {
            /** @var \Illuminate\Auth\SessionGuard $guard */
            $guard = auth();
            $isValid = $guard->validate([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            return $isValid ? $guard->getLastAttempted() : null;
        });

        ResetPassword::toMailUsing(function (User $user, string $token) {
            $query = [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ];

            $queryString = http_build_query($query);

            $url = url("/access/set?$queryString");

            return (new MailMessage)
                ->subject(trans('mail/resetPassword.subject'))
                ->markdown('emails.reset-password', [
                    'name' => $user->name,
                    'url' => $url,
                ]);
        });

        VerifyEmail::createUrlUsing(function (User $notifiable) {
            URL::forceRootUrl(config('app.url'));

            $params = [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ];

            $request = resolve(Request::class);
            $forwardedHost = $request->forwardedHost();
            if ($forwardedHost) {
                $params['forwarded-host'] = $forwardedHost;
            }

            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                $params,
            );
        });

        VerifyEmail::toMailUsing(function (User $user, string $url) {
            return (new MailMessage)->markdown('emails.activate-account', [
                'name' => $user->name,
                'url' => $url,
            ]);
        });

        if (app()->environment('production', 'staging')) {
            URL::forceScheme('https');
        }

        Gate::define('viewWebSocketsDashboard', function () {
            return app()->environment() !== 'production';
        });

        Passport::hashClientSecrets();
        Passport::useClientModel(Client::class);
    }
}
