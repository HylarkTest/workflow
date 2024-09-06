<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertTrue;

use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Auth\UpdateEmailController;
use App\Notifications\Auth\EmailUpdatedNotification;
use App\Notifications\Auth\OneTimePasswordNotification;
use App\Notifications\Contracts\CustomEmailNotification;

uses(RefreshDatabase::class);

function getValidFormData(array $attributes = []): array
{
    $defaultAttributes = [
        'email' => fake()->email,
        'password' => 'password',
    ];

    return array_merge($defaultAttributes, $attributes);
}

test('a user can update their email address', function (): void {
    Notification::fake();

    $user = createUser(['email' => $oldEmail = fake()->email]);
    $formData = getValidFormData();

    // The user requests a change of email address
    $this->be($user)
        ->post('user/email', $formData)
        ->assertSuccessful()
        ->assertSessionHas(UpdateEmailController::SESSION_KEY, $formData['email']);

    // The user revieves a one-time-password to verify the change
    Notification::assertSentTo(
        $user,
        OneTimePasswordNotification::class,
        function (OneTimePasswordNotification $notification) use ($formData) {
            assertTrue($notification instanceof CustomEmailNotification);

            return $notification->getEmailAddress() === $formData['email'];
        }
    );

    // The user submits the one-time-password to verify the change
    $this->be($user)->post('user/email/verify', [
        'code' => cache()->get("one_time_password:{$user->id}"),
    ])->assertSuccessful();

    $user = $user->fresh();

    expect($user->email)->toBe($formData['email']);
    expect($user->email_verified_at)->not(null);

    // The user reveives a warning at their original email address notifying them of the change
    Notification::assertSentTo(
        $user,
        EmailUpdatedNotification::class,
        function (EmailUpdatedNotification $notification) use ($oldEmail) {
            assertTrue($notification instanceof CustomEmailNotification);

            return $notification->getEmailAddress() === $oldEmail;
        }
    );
});

test('a user action is recorded on a successful email update', function (): void {
    $user = createUser();

    $this->travel(1)->hours();

    enableAllActions();

    $this->be($user)->post('user/email', getValidFormData());
    $this->be($user)->post('user/email/verify', [
        'code' => cache()->get("one_time_password:{$user->id}"),
    ]);

    expect($user->actions->count())->toBe(2);
});

test('a request to update a users email address must be password validated', function (): void {
    $user = createUser();

    $this->be($user)->post('user/email', [
        'currentEmail' => $user->email,
        'email' => fake()->email,
        'password' => null,
    ])->assertInvalid(['password']);

    $this->be($user)->post('user/email', [
        'currentEmail' => $user->email,
        'email' => fake()->email,
        'password' => 'pass123',
    ])->assertInvalid(['password']);
});

test('a request to update a users email address must use a unique email address', function (): void {
    $duplicateEmail = fake()->email;
    createUser(['email' => $duplicateEmail]);
    $user = createUser();

    $this->be($user)->post('user/email', [
        'currentEmail' => $user->email,
        'email' => $duplicateEmail,
        'password' => 'password',
    ])->assertInvalid(['email']);
});

test('a user cannot request to update their old email address to the same email address', function (): void {
    $user = createUser();

    $this->be($user)->post('user/email', [
        'currentEmail' => $user->email,
        'email' => $user->email,
        'password' => 'password',
    ])->assertInvalid(['email']);
});

test('a user cannot try resending more than 5 one-time-passwords per minute', function (): void {
    $user = createUser();

    $formData = getValidFormData(['email' => fake()->email()]);

    for ($i = 0; $i < 5; $i++) {
        $this->be($user)->post('user/email', $formData)->assertSuccessful();
    }

    $this->withExceptionHandling()->be($user)->post('user/email', $formData)->assertStatus(429);
    $this->travel(1)->minutes();
    $this->be($user)->post('user/email', $formData)->assertSuccessful();
});
