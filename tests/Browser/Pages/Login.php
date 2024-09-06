<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Login extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function fillLogin(Browser $browser, $email = Registration::EMAIL, $password = Registration::PASSWORD): void
    {
        $emailInput = $browser->resolver->resolveForField('email');
        if ($emailInput->isEnabled()) {
            $browser->type('email', $email);
        }
        $browser->type('password', $password)
            ->press('Sign in')
            ->waitUntilMissing('.o-login-page', 20);
    }

    public function goToForgotPassword(Browser $browser): void
    {
        $browser->clickLink('I don\'t know my password')
            ->on(new ForgotPassword);
    }
}
