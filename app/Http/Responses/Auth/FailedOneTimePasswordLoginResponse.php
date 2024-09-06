<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse;

class FailedOneTimePasswordLoginResponse extends FailedTwoFactorLoginResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $message = __('The provided one time password was invalid.');

        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['code' => [$message]]);
        }

        return redirect()->route('two-factor.login')->withErrors(['code' => $message]);
    }
}
