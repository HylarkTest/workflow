<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Actions\ConfirmPassword;
use Illuminate\Validation\ValidationException;

class RequirePassword
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected StatefulGuard $guard) {}

    public function handle(Request $request, \Closure $next): mixed
    {
        $confirmed = app(ConfirmPassword::class)(
            $this->guard, $request->user(), $request->input('password')
        );

        if (! $confirmed) {
            throw ValidationException::withMessages(['password' => trans('validation.auth')]);
        }

        return $next($request);
    }
}
