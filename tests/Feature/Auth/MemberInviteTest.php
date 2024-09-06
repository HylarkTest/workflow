<?php

declare(strict_types=1);

use App\Models\Base;
use App\Models\User;
use App\Core\BaseType;
use App\Core\Groups\Role;
use App\Mail\CollabInvite;
use App\Core\MemberActionType;
use Illuminate\Support\Facades\Mail;
use App\Notifications\TooManyBasesForInvite;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\CollabInvite as CollabInviteNotification;

uses(RefreshDatabase::class);

test('someone can be invited to a collaborative base', function () {
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        return $mail->hasTo('test@mail.com');
    });

    $base = $admin->bases()->get()->last();

    expect($base->memberInvites()->count())->toBe(1);
});

test('someone cannot be invited to a personal base', function () {
    $this->withExceptionHandling();
    $user = createUser();

    Mail::fake();

    $this->be($user)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertForbidden();

    Mail::assertNotSent(CollabInvite::class);
});

test('members cannot invite anyone to a base', function () {
    $this->withExceptionHandling();
    $member = createMemberUser();

    Mail::fake();

    $this->be($member)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertForbidden();

    Mail::assertNotSent(CollabInvite::class);
});

test('an existing user can be invited to a collaborative base', function () {
    $admin = createAdminUser();
    $user = createUser(['email' => 'test@mail.com']);

    Notification::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Notification::assertSentTo([$user], CollabInviteNotification::class);

    $base = $admin->bases()->get()->last();

    expect($base->memberInvites()->count())->toBe(1);
});

test('only owners can invite other owners', function () {
    $this->withExceptionHandling();
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::OWNER->value,
    ])->assertForbidden();

    Mail::assertNotSent(CollabInvite::class);

    $owner = createOwnerUser($admin->bases()->get()->last());

    $this->be($owner)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::OWNER->value,
    ])->assertSuccessful();

    Mail::assertQueued(CollabInvite::class);
});

test('an invite can only be sent to the same email address every 24 hours', function () {
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        return $mail->hasTo('test@mail.com');
    });

    Mail::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertJsonValidationErrors('emails');

    Mail::assertNotSent(CollabInvite::class);

    $this->travel(1)->day();

    Mail::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        return $mail->hasTo('test@mail.com');
    });
});

test('old invites do not show', function () {
    $admin = createAdminUser();
    $invitedUser = createUser();

    Notification::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => [$invitedUser->email],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Notification::assertSentTo($invitedUser, CollabInviteNotification::class);

    $this->travel(1)->day();

    Notification::fake();

    $this->be($admin)->postJson('member-invite', [
        'emails' => [$invitedUser->email],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Notification::assertSentTo($invitedUser, CollabInviteNotification::class, function (CollabInviteNotification $notification) use ($invitedUser) {
        $this->be($invitedUser)->get($notification->invite->getInviteLink());

        return true;
    });

    $this->be($admin)->assertGraphQL([
        'base' => [
            'node' => [
                'invites(status: PENDING)' => new NullFieldWithSubQuery('{ email }', true),
            ],
        ],
    ])->assertJsonCount(0, 'data.base.node.invites');
});

test('clicking the invite link goes to registration', function () {
    $this->withoutExceptionHandling();
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    auth()->logout();

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) use ($admin) {
        $this->get($mail->inviteLink)
            ->assertRedirect('signup?email=test@mail.com')
            ->assertSessionHas('member-invite');

        $this->postJson('register', [
            'name' => 'Test User',
            'email' => 'test@mail.com',
            'password' => 'Secret123',
            'permission' => true,
        ])->assertSuccessful();

        $user = User::latest('id')->first();
        expect($user->bases->count())->toBe(2);
        expect($user->bases->last()->id)->toBe($admin->bases->last()->id);

        return true;
    });
});

test('clicking the invite link when logged in with a different email logs out', function () {
    $this->withoutExceptionHandling();
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    auth()->logout();

    $user = createUser(['email' => 'test2@mail.com']);
    auth()->login($user);

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        $this->get($mail->inviteLink)
            ->assertRedirect('signup?email=test@mail.com')
            ->assertSessionHas('member-invite');

        expect(auth()->check())->toBeFalse();

        return true;
    });
});

test('clicking the invite link when logged in accepts the invite', function () {
    $this->withoutExceptionHandling();
    $admin = createAdminUser();

    $user = createUser(['email' => 'test@mail.com']);

    $this->be($admin);

    Notification::fake();

    $this->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    auth()->login($user);

    Notification::assertSentTo($user, CollabInviteNotification::class, function (CollabInviteNotification $mail) use ($user, $admin) {
        $this->get($mail->invite->getInviteLink())
            ->assertRedirect('/')
            ->assertSessionMissing('member-invite');

        $user->refresh();
        expect(auth()->user()->id)->toBe($user->id);
        expect($user->bases->count())->toBe(2);
        expect($user->bases->last()->id)->toBe($admin->bases->last()->id);

        return true;
    });
});

test('clicking the invite link when logged out goes to login', function () {
    $this->withoutExceptionHandling();
    $admin = createAdminUser();
    $user = createUser(['email' => 'test@mail.com']);

    Notification::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    auth()->logout();

    Notification::assertSentTo($user, CollabInviteNotification::class, function (CollabInviteNotification $mail) use ($admin, $user) {
        $this->get($mail->invite->getInviteLink())
            ->assertRedirect('login?email=test@mail.com')
            ->assertSessionHas('member-invite');

        $this->postJson('login', [
            'email' => 'test@mail.com',
            'password' => 'password',
        ])->assertSuccessful();

        $user = User::latest('id')->first();
        expect($user->bases->count())->toBe(2);
        expect($user->bases->last()->id)->toBe($admin->bases->last()->id);

        return true;
    });
});

test('registering with a different email address shows an error', function () {
    $admin = createAdminUser();

    Mail::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    auth()->logout();

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        $this->get($mail->inviteLink)
            ->assertRedirect('signup?email=test@mail.com')
            ->assertSessionHas('member-invite');

        $this->postJson('register', [
            'name' => 'Test User',
            'email' => 'test2@mail.com',
            'password' => 'Secret123',
            'permission' => true,
        ])->assertJsonValidationErrors('email');

        return true;
    });
});

test('an invalid invite link shows an error', function () {
    $this->withExceptionHandling();
    $user = createAdminUser();
    $base = $user->bases->last();
    $invite = $base->memberInvites()->create([
        'email' => 'test@mail.com',
        'role' => Role::MEMBER->value,
        'token' => 'abc',
        'inviter_id' => $user->id,
    ]);
    $this->getJson(route('member-invite.accept', ['invite' => $invite, 'token' => 'def']))
        ->assertForbidden();
});

test('the invite link is valid for 24 hours', function () {
    $this->withExceptionHandling();
    $user = createAdminUser();
    $base = $user->bases->last();
    $invite = $base->memberInvites()->create([
        'email' => 'test@mail.com',
        'role' => Role::MEMBER->value,
        'token' => 'abc',
        'inviter_id' => $user->id,
    ]);
    $this->travel(25)->hours();
    $this->getJson(route('member-invite.accept', ['invite' => $invite, 'token' => 'abc']))
        ->assertForbidden();
});

test('the user can request a new invite link with a new token', function () {
    $user = createAdminUser();
    $base = $user->bases->last();
    $invite = $base->memberInvites()->create([
        'email' => 'test@mail.com',
        'role' => Role::MEMBER->value,
        'token' => 'abc',
        'inviter_id' => $user->id,
    ]);

    Mail::fake();
    $this->get(app('url')->signedRoute('member-invite.resend', ['invite' => $invite]))
        ->assertRedirect('/invite-sent');

    Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) {
        expect($mail->resend)->toBeTrue();
        $this->get($mail->inviteLink)
            ->assertRedirect('signup?email=test@mail.com')
            ->assertSessionHas('member-invite');

        return true;
    });
});

test('sending an invite adds an action to the base', function () {
    config(['actions.automatic' => true]);
    $admin = createAdminUser();
    $base = $admin->bases->last();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    expect($base->latestAction->type->is(MemberActionType::MEMBER_INVITED()))->toBeTrue();
});

test('resending an invite adds an action to the base', function () {
    config(['actions.automatic' => true]);
    $admin = createAdminUser();
    $base = $admin->bases->last();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    $this->travel(25)->hours();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    expect($base->latestAction->type->is(MemberActionType::MEMBER_INVITE_RESENT()))->toBeTrue();
});

test('accepting an invite adds an action to the base', function () {
    config(['actions.automatic' => true]);
    $user = createAdminUser();
    $base = $user->bases->last();
    $invite = $base->memberInvites()->create([
        'email' => 'test@mail.com',
        'role' => Role::MEMBER->value,
        'token' => 'abc',
        'inviter_id' => $user->id,
    ]);

    $user = createUser(['email' => 'test@mail.com']);
    auth()->login($user);
    $this->getJson(route('member-invite.accept', ['invite' => $invite, 'token' => 'abc']));

    tenancy()->initialize($base);
    expect($base->latestAction->type->is(MemberActionType::MEMBER_INVITE_ACCEPTED()))->toBeTrue();
});

test('a different email is sent to a user who is connected to too many bases', function () {
    $admin = createAdminUser();
    $user = createUser(['email' => 'test@mail.com']);
    for ($i = 0; $i < 8; $i++) {
        $base = Base::query()->create(['name' => 'Test Base', 'type' => BaseType::COLLABORATIVE]);
        $user->bases()->attach($base, ['role' => Role::MEMBER]);
    }

    Notification::fake();

    $this->be($admin)->post('member-invite', [
        'emails' => ['test@mail.com'],
        'role' => Role::MEMBER->value,
    ])->assertSuccessful();

    Notification::assertSentTo($user, TooManyBasesForInvite::class);

    $base = $admin->bases()->latest('id')->first();

    expect($base->memberInvites)->toBeEmpty();
});

test('an invite can be deleted', function () {
    $admin = createAdminUser();
    $base = $admin->bases->last();
    $invite = $base->memberInvites()->create([
        'email' => 'test@mail.com',
        'role' => Role::MEMBER,
        'inviter_id' => $admin->id,
        'token' => 'abc',
    ]);

    $this->be($admin)->deleteJson(route('member-invite.destroy', ['email' => 'test@mail.com']))
        ->assertSuccessful();

    expect($invite->fresh())->deleted_at->not->toBeNull();
});

test('logging in from a deleted invite does not connect the user to the base', function () {
    $this->withExceptionHandling();
    $admin = createAdminUser();
    $base = $admin->bases->last();
    $user = createUser();
    $invite = $base->memberInvites()->create([
        'email' => $user->email,
        'role' => Role::MEMBER,
        'inviter_id' => $admin->id,
        'token' => 'abc',
    ]);
    auth()->logout();

    $this->getJson(route('member-invite.accept', ['invite' => $invite, 'token' => 'abc']))
        ->assertRedirect('/login?email='.$user->email);

    $base->run(fn () => $invite->delete());

    $this->postJson('login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSuccessful();

    expect($user->bases->count())->toBe(1)
        ->and($user->bases->first()->id)->not->toBe($base->id);
});

test('a member role can be updated', function () {
    $admin = createAdminUser();
    $user = createUser();
    $base = $admin->bases->last();
    $user->bases()->attach($base, ['role' => Role::MEMBER]);
    $userWithPivot = $base->members()->find($user->id);

    tenancy()->initialize($base);
    enableAllActions();
    $this->be($admin)->assertGraphQLMutation(
        "updateMember(input: { id: \"{$userWithPivot->pivot->global_id}\", role: ADMIN })"
    );

    $userWithPivot = $base->members()->find($user->id);
    expect($userWithPivot->pivot->role)->toBe(Role::ADMIN);

    $action = $base->baseActions()->latest('id')->first();
    expect($action->type->is(MemberActionType::MEMBER_ROLE_UPDATED()))->toBeTrue()
        ->and($action->payload)->toBe(['changes' => ['role' => 'ADMIN'], 'original' => ['role' => 'MEMBER']]);
});

test('a member cannot change the role of another member', function () {
    $this->withGraphQLExceptionHandling();
    $admin = createAdminUser();
    $user1 = createUser();
    $user2 = createUser();
    $base = $admin->bases->last();
    $user1->bases()->attach($base, ['role' => Role::MEMBER]);
    $user2->bases()->attach($base, ['role' => Role::MEMBER]);
    $user1WithPivot = $base->members()->find($user1->id);

    switchToBase($base, $user2);

    $this->be($user2->fresh())->assertFailedGraphQLMutation(
        "updateMember(input: { id: \"{$user1WithPivot->pivot->global_id}\", role: ADMIN })"
    );
});

test('only an owner can update a member to an owner', function () {
    $this->withGraphQLExceptionHandling();
    $admin = createAdminUser();
    $user = createUser();
    $base = $admin->bases->last();
    $user->bases()->attach($base, ['role' => Role::MEMBER]);
    $userWithPivot = $base->members()->find($user->id);

    $this->be($admin)->assertFailedGraphQLMutation(
        "updateMember(input: { id: \"{$userWithPivot->pivot->global_id}\", role: OWNER })"
    );
});

test('a member can be deleted', function () {
    $admin = createAdminUser();
    $user = createUser();
    $base = $admin->bases->last();
    $user->bases()->attach($base, ['role' => Role::MEMBER]);
    $userWithPivot = $base->members()->find($user->id);

    $this->be($admin)->assertGraphQLMutation(
        "deleteMember(input: { id: \"{$userWithPivot->pivot->global_id}\" })"
    );

    $userWithPivot = $base->members()->find($user->id);
    expect($userWithPivot)->toBeNull();
});

test('a member cannot delete another member', function () {
    $this->withGraphQLExceptionHandling();
    $admin = createAdminUser();
    $user1 = createUser();
    $user2 = createUser();
    $base = $admin->bases->last();
    $user1->bases()->attach($base, ['role' => Role::MEMBER]);
    $user2->bases()->attach($base, ['role' => Role::MEMBER]);
    $user1WithPivot = $base->members()->find($user1->id);

    switchToBase($base, $user2);

    $this->be($user2->fresh())->assertFailedGraphQLMutation(
        "deleteMember(input: { id: \"{$user1WithPivot->pivot->global_id}\" })"
    );
});

function createOwnerUser(?Base $base = null): User
{
    return createCollabUser(Role::OWNER, $base);
}

function createAdminUser(?Base $base = null): User
{
    return createCollabUser(Role::ADMIN, $base);
}

function createMemberUser(?Base $base = null): User
{
    return createCollabUser(Role::MEMBER, $base);
}
