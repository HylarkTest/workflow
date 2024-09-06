<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request)
    {
        if ($request->session()->exists('promptedForLogin')) {
            return $request->expectsJson()
                ? response()->json([
                    'redirect' => redirect()->getIntendedUrl() ?: Fortify::redirects('login'),
                ])
                : redirect()->intended(Fortify::redirects('login'));
        }

        return parent::toResponse($request);
    }
}
