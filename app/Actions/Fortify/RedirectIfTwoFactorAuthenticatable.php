<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseAction;

class RedirectIfTwoFactorAuthenticatable extends BaseAction
{
    /**
     * Get the two factor authentication enabled response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('two-factor.login');
    }
}
