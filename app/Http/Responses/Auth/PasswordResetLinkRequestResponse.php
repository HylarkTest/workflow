<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;

class PasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract, SuccessfulPasswordResetLinkRequestResponse
{
    public function __construct(protected string $status) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // For security reasons we don't want to indicate to the user that a
        // particular email exists on the site so we will say that a link was
        // sent if an account exists.
        if ($this->status === Password::INVALID_USER || $this->status === Password::RESET_LINK_SENT) {
            return new JsonResponse(['status' => Password::RESET_LINK_SENT], 200);
        }

        throw ValidationException::withMessages(['email' => [trans($this->status)]]);
    }
}
