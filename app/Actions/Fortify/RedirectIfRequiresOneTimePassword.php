<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;

class RedirectIfRequiresOneTimePassword extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     */
    public function handle($request, $next)
    {
        /** @var \App\Models\User|null $user */
        $user = $this->validateCredentials($request);

        if ($user && ! $user->hasEnabledTwoFactorAuthentication()
            && $user->isSuspiciousRequest($request)) {
            return $this->signInCodeChallengeResponse($request, $user);
        }

        return $next($request);
    }

    /**
     * Get the sign in code authentication enabled response.
     *
     * @throws \Exception
     */
    protected function signInCodeChallengeResponse(Request $request, User $user): Response
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        $user->sendOneTimePassword($request, now()->addMinutes(5));

        return response()->json(['one_time_password' => true]);
    }
}
