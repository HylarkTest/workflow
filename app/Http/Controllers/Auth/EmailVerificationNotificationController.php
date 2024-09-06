<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect('/activate?already-verified=1');
        }

        $user->sendEmailVerificationNotification();

        return redirect('/activation-sent');
    }
}
