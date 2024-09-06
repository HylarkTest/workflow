<?php

declare(strict_types=1);

use App\Core\Groups\Role;
use Laravel\Dusk\Browser;
use App\Mail\CollabInvite;
use App\Models\MemberInvite;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\MailHog;
use Illuminate\Support\Facades\Mail;
use Tests\Browser\Pages\Registration;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

test('a user can accept an invite in the same browser as sending', function () {
    $this->browse(function (Browser $browser) {
        $user = createCollabUser();
        $base = $user->bases->last();

        $browser->logout();
        $browser->loginAs($user);

        Mail::fake();
        MemberInvite::createAndSend($base, $user, Registration::EMAIL, Role::MEMBER);

        Mail::assertQueued(CollabInvite::class, function (CollabInvite $mail) use ($browser) {
            $browser->visit($mail->inviteLink);

            $browser->whenLoaded()
                ->on(new Registration)
                ->acceptCookies();

            $browser->fillRegisterForm()
                ->waitUntilMissingText('Join', 20);

            return true;
        });
    });
});

test('a user with an account sees the invited base when they log in', function () {
    $this->browse(function (Browser $browser) {
        $admin = createCollabUser();
        $base = $admin->bases->last();
        $user = createUser();

        $browser->logout();
        $browser->loginAs($admin);

        Notification::fake();
        MemberInvite::createAndSend($base, $user, $user->email, Role::MEMBER);

        Notification::assertSentTo($user, function (App\Notifications\CollabInvite $notification) use ($browser, $user, $base) {
            $browser->visit($notification->invite->getInviteLink());

            $browser->whenLoaded()
                ->on(new Login)
                ->acceptCookies();

            $browser->fillLogin($user->email, 'password')
                ->whenLoaded()
                ->assertSee($base->name);

            return true;
        });
    });
});

test('a user with an account sees an error when invited with an expired link', function () {
    $this->browse(function (Browser $browser) {
        $admin = createCollabUser();
        $base = $admin->bases->last();
        $user = createUser();

        $browser->logout();

        Notification::fake();
        MemberInvite::createAndSend($base, $user, $user->email, Role::MEMBER);
        MemberInvite::createAndSend($base, $user, $user->email, Role::MEMBER);

        Notification::assertSentTo($user, function (App\Notifications\CollabInvite $notification) use ($browser, $user) {
            /** @var \App\Mail\CollabInvite $mail */
            $mail = $notification->toMail($user);
            $browser->visit($mail->inviteLink)->whenLoaded();

            $browser->whenLoaded()
                ->on(new Login)
                ->acceptCookies()
                ->fillLogin($user->email, 'password')
                ->tinker();

            return true;
        });
    });
});

test('a user with an account can accept an invite after OTP request', function () {
    $this->browse(function (Browser $browser, Browser $mailhog) {
        $admin = createCollabUser();
        $base = $admin->bases->last();
        $user = createUser();
        $user->loginAttempts()->create([
            'ip' => '127.0.0.2',
            'succeeded' => true,
            'user_agent' => 'Dusk',
        ]);

        $browser->logout();

        Notification::fake();
        MemberInvite::createAndSend($base, $user, $user->email, Role::MEMBER);

        Notification::assertSentTo($user, function (App\Notifications\CollabInvite $notification) use ($browser, $mailhog, $user, $base) {
            $browser->visit($notification->invite->getInviteLink());
            $this->clearEmails();
            $mailhog->visit(new MailHog);

            $browser->whenLoaded()
                ->on(new Login)
                ->acceptCookies()
                ->fillLogin($user->email, 'password')
                ->whenLoaded();

            $mailhog->waitFor('@messages', 20)
                ->openLastEmail('Hylark sign-in verification');

            $code = '';
            $mailhog->inPreview(function (Browser $preview) use (&$code) {
                $code = $preview->resolver->findOrFail('@code + h2')->getText();
            })->quit();

            $browser->type('code', $code)
                ->press('Submit')
                ->waitFor('.c-loader-main', 20)
                ->whenLoaded()
                ->assertSee($base->name);

            return true;
        });
    });
});
