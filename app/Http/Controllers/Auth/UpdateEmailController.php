<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use App\Notifications\Auth\EmailUpdatedNotification;
use App\Http\Requests\Auth\VerifyOneTimePasswordRequest;

class UpdateEmailController extends Controller
{
    public const SESSION_KEY = 'pending_email';

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $request->validate([
            'email' => 'required|email:rfc,strict,filter|unique:users',
            'password' => 'current_password',
        ]);

        $pendingEmailAddress = $request->get('email');

        session([self::SESSION_KEY => $pendingEmailAddress]);

        $timeout = now()->addMinutes(5);
        $user->sendOneTimePassword($request, $timeout, $pendingEmailAddress);

        return response()->json([
            'expiration' => $timeout,
        ]);
    }

    public function verify(VerifyOneTimePasswordRequest $request): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $request->hasValidCode()) {
            $message = trans('validation.invalid', ['attribute' => trans('common.otp')]);

            throw ValidationException::withMessages(['code' => [$message]]);
        }

        $user->forgetOneTimePassword();

        $pendingEmail = session(self::SESSION_KEY);

        if (! $pendingEmail) {
            report('No pending email address found in session storage to update to.');
            abort(500, trans('errors.error_pages.default.explanation'));
        }

        $oldEmail = $user->email;

        $user->update(['email' => $pendingEmail]);
        $user->markEmailAsVerified();

        $user->notifyNow(new EmailUpdatedNotification($user, $oldEmail));

        Subscription::broadcast('meUpdated', $user);

        return response(status: 200);
    }
}
