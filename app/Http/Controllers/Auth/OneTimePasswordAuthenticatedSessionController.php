<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\OneTimePasswordLoginRequest;
use App\Http\Responses\Auth\OneTimePasswordLoginResponse;

class OneTimePasswordAuthenticatedSessionController extends Controller
{
    public function __construct(protected StatefulGuard $guard) {}

    public function store(OneTimePasswordLoginRequest $request): mixed
    {
        $user = $request->challengedUser();

        if (! $request->hasValidCode()) {
            $message = __('The provided one time password was invalid.');

            throw ValidationException::withMessages(['code' => [$message]]);
        }

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        $user->forgetOneTimePassword();

        return app(OneTimePasswordLoginResponse::class);
    }
}
