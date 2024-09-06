<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\BaseType;
use App\Models\MemberInvite;
use Illuminate\Http\Request;
use App\Events\Auth\MemberLeft;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use App\Events\Billing\AccountDeleted;
use Illuminate\Contracts\Auth\StatefulGuard;
use Symfony\Component\HttpFoundation\Response;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class AccountController extends Controller
{
    public function destroy(Request $request, AuthManager $auth): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $guard = $auth->guard();

        if ($guard instanceof StatefulGuard) {
            $auth->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $user->delete();

        $user->bases()->where('type', BaseType::COLLABORATIVE)
            ->eachById(fn ($base) => event(new MemberLeft($user, $base)), 1000, 'bases.id', 'id');
        $user->memberInvites()
            ->eachById(fn (MemberInvite $invite) => Subscription::broadcast('memberInviteDeleted', $invite, true));

        AccountDeleted::dispatch($user);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
