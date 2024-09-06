<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Support\Responsable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController as BasePasswordResetLinkController;

class PasswordResetLinkController extends BasePasswordResetLinkController
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Responsable
    {
        $key = Fortify::email();

        $request->validate([$key => 'required|email']);

        $request->merge([$key => $request->input($key)]);

        $status = $this->broker()->sendResetLink(
            $request->only($key)
        );

        return $status === Password::RESET_LINK_SENT
            ? app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => $status])
            : app(FailedPasswordResetLinkRequestResponse::class, ['status' => $status]);
    }
}
