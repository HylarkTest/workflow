<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Sentry\State\Scope;
use Illuminate\Support\Arr;

use function Sentry\configureScope;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string[]  ...$guards
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        /** @phpstan-ignore-next-line */
        $response = parent::handle($request, $next, ...$guards);

        configureScope(function (Scope $scope) use ($request): void {
            /** @var \App\Models\User|null $user */
            $user = $this->auth->guard()->user();
            if ($user) {
                $scope->setUser([
                    'id' => $user->id,
                    ...(app()->environment('production') && ! $user->shouldShowInSentry() ? [] : [
                        'email' => $user->email,
                        'username' => $user->name,
                    ]),
                    'ip_address' => Arr::last($request->ips()),
                ]);
            }
        });

        return $response;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            return '/login?redirect='.urlencode($request->fullUrl());
        }

        return '';
    }
}
