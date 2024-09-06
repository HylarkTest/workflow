<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\User;
use App\Core\Groups\Role;
use App\Models\MemberInvite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Enum;
use App\Exceptions\ClientAwareHttpException;
use App\Notifications\TooManyBasesForInvite;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class MemberController extends Controller
{
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email',
            'role' => ['required', new Enum(Role::class)],
        ]);

        /** @var \App\Models\Base $base */
        $base = tenant();

        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($base->isPersonal()) {
            abort(Response::HTTP_FORBIDDEN, 'You cannot invite members to your personal base.');
        }

        $role = Role::from($data['role']);

        if ($role === Role::OWNER) {
            $this->authorize('addOwner', $base);
        }

        $emailsAlreadyMembers = $base->members()->whereIn('email', $data['emails'])->pluck('email');

        if ($emailsAlreadyMembers->isNotEmpty()) {
            throw ValidationException::withMessages(['emails' => [trans_choice('validation.custom.invite.email.member', $emailsAlreadyMembers->count(), ['emails' => $emailsAlreadyMembers->implode(', ')])]]);
        }

        $emails = $data['emails'];

        $emailsAlreadyInvitedRecently = $base->memberInvites()
            ->withTrashed()
            ->whereIn('email', $emails)
            ->where('created_at', '>', now()->subDay())
            ->pluck('email');

        if ($emailsAlreadyInvitedRecently->isNotEmpty()) {
            throw ValidationException::withMessages(['emails' => [trans_choice('validation.custom.invite.email.invited', $emailsAlreadyInvitedRecently->count(), ['emails' => $emailsAlreadyInvitedRecently->implode(', ')])]]);
        }

        foreach ($emails as $email) {
            /** @var \App\Models\User|null $existingUser */
            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser && $existingUser->bases()->count() >= Base::MAX_BASES) {
                $existingUser->notify(new TooManyBasesForInvite($base, $user));
            } else {
                MemberInvite::createAndSend($base, $user, $email, $role);
            }
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function update(string $email, Request $request): Response
    {
        $role = $request->validate([
            'role' => ['required', new Enum(Role::class)],
        ])['role'];
        $role = Role::from($role);

        /** @var \App\Models\Base $base */
        $base = tenant();

        $invite = $base->memberInvites()->pending()->where('email', $email)->firstOrFail();
        if ($invite->role !== $role) {
            $base->memberInvites()
                ->pending()
                ->where('email', $email)
                ->update(['role' => $role->value]);

            Subscription::broadcast('memberInviteUpdated', $invite);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function destroy(string $email, Request $request): Response
    {
        /** @var \App\Models\Base $base */
        $base = tenant();

        $invite = $base->memberInvites()->pending()->where('email', $email)->firstOrFail();
        $base->memberInvites()
            ->pending()
            ->where('email', $email)
            ->delete();

        Subscription::broadcast('memberInviteDeleted', $invite);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function accept(int|string $inviteId, Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        // Fix error if someone types random stuff in the url
        /** @var ?MemberInvite $invite */
        $invite = is_numeric($inviteId) ? MemberInvite::query()->find($inviteId) : null;
        /** @var string|null $token */
        $token = $request->query('token');
        if (! $invite || ! $token || $invite->hasExpired() || ! $invite->verifyToken($token)) {
            throw new ClientAwareHttpException(Response::HTTP_FORBIDDEN, trans('errors.invite.403.token'), explanation: '');
        }
        if ($user) {
            if ($user->bases->count() >= Base::MAX_BASES) {
                throw new ClientAwareHttpException(Response::HTTP_UNPROCESSABLE_ENTITY, trans('errors.invite.422.limit'), explanation: '');
            }
            if (strcasecmp($user->email, $invite->email) === 0) {
                $user->acceptMemberInvite($invite);
                $user->setActiveBase($invite->base);

                return response()->redirectTo('/');
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        session()->put('member-invite', $invite->id);

        if ($invite->existingUser()) {
            return response()->redirectTo("/login?email={$invite->email}");
        }

        return response()->redirectTo("/signup?email={$invite->email}");
    }

    public function resend(int|string $inviteId): RedirectResponse
    {
        /** @var ?MemberInvite $invite */
        $invite = is_numeric($inviteId) ? MemberInvite::query()->find($inviteId) : null;

        if (! $invite) {
            throw new ClientAwareHttpException(Response::HTTP_NOT_FOUND, trans('errors.invite.404'), explanation: '');
        }

        $newToken = MemberInvite::generateToken();
        $invite->update([
            'token' => $newToken,
            'expires_at' => now()->addDay(),
        ]);
        $invite->sendInvitation($newToken);

        return response()->redirectTo('/invite-sent');
    }
}
