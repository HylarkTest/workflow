<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\Auth\OneTimePasswordNotification;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

uses(RefreshDatabase::class);

test('a user is required to enter a code sent to their email when logging in from a new browser', function (): void {
    $this->withoutExceptionHandling();
    Notification::fake();

    $user = createUser(['email' => 'test@example.com']);

    // First login is fine
    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertSuccessful()->assertJson(['two_factor' => false]);

    expect(auth()->check())->toBeTrue();

    auth()->logout();

    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ], ['REMOTE_ADDR' => '127.0.0.2'])->assertSuccessful()->assertJson(['one_time_password' => true]);

    $this->assertGuest();

    Notification::assertSentTo($user, function (OneTimePasswordNotification $mail) use ($user) {
        $code = $mail->code;

        $this->postJson('/one-time-password', ['code' => $code])->assertSuccessful();

        $this->assertAuthenticatedAs($user);

        $html = (string) $mail->toMail()->render();

        expect($html)->toContain($code)
            ->and($html)->toContain('127.0.0.2')
            ->and($html)->toContain('Farnborough, United Kingdom (Estimated)');

        return true;
    });
});

test('the one time password expires after 5 minutes', function (): void {
    // Redis cache is necessary for fast tests but doesn't support time travel.
    config(['cache.default' => 'array']);
    Notification::fake();

    $user = createUser(['email' => 'test@example.com']);

    login();

    auth()->logout();

    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ], ['REMOTE_ADDR' => '127.0.0.2'])->assertSuccessful()
        ->assertJson(['one_time_password' => true]);

    $this->assertGuest();

    Notification::assertSentTo($user, function (OneTimePasswordNotification $mail) {
        $code = $mail->code;

        $this->travel(6)->minutes();

        $this->postJson('/one-time-password', ['code' => $code])
            ->assertJsonValidationErrors('code');

        $this->assertGuest();

        return true;
    });
});

test('a user is not required to enter a sign in code when logging in from the same ip address', function (): void {
    Notification::fake();

    $user = createUser([
        'email' => 'test@example.com',
    ]);

    $user->loginAttempts()->save(
        (new LoginAttempt)->forceFill(['ip' => '127.0.0.1', 'succeeded' => true, 'user_agent' => 'Symfony'])
    );

    login();

    $this->assertAuthenticatedAs($user);

    Notification::assertNothingSent();
});

test('sign in code authentication is not required if 2fa is enabled', function (): void {
    Notification::fake();

    $user = createUser([
        'email' => 'test@example.com',
        'two_factor_confirmed_at' => now(),
    ]);

    $enable2fa = resolve(EnableTwoFactorAuthentication::class);
    $enable2fa($user);

    login();

    $recoveryCode = $user->recoveryCodes()[0];

    $this->postJson('/two-factor-challenge', ['recovery_code' => $recoveryCode])
        ->assertSuccessful();

    $this->assertAuthenticatedAs($user);

    Notification::assertNotSentTo($user, OneTimePasswordNotification::class);
});

test('a user is not remembered after 2fa if they set it to false', function (): void {
    $user = createUser([
        'email' => 'test@example.com',
        'two_factor_confirmed_at' => now(),
    ]);

    $enable2fa = resolve(EnableTwoFactorAuthentication::class);
    $enable2fa($user);

    login(remember: false);

    $recoveryCode = $user->recoveryCodes()[0];

    $response = $this->postJson('/two-factor-challenge', ['recovery_code' => $recoveryCode])
        ->assertSuccessful();

    foreach ($response->headers->getCookies() as $cookie) {
        if (Str::startsWith($cookie->getName(), 'remember_web')) {
            $this->fail('The remember cookie was set');
        }
    }

    $this->assertAuthenticatedAs($user);
});

// Helpers
function login(string $email = 'test@example.com', string $password = 'password', bool $remember = true): TestResponse
{
    return test()->postJson('/login', [
        'email' => $email,
        'password' => $password,
        'remember' => $remember,
    ])->assertSuccessful();
}
