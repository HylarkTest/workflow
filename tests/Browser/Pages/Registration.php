<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Registration extends Page
{
    public const EMAIL = 'bobby@example.test';

    public const PASSWORD = 'Secret1!';

    public function url(): string
    {
        return '/signup';
    }

    public function assert(Browser $browser): void
    {
        $browser->whenLoaded()->assertSee('Hello');
    }

    public function fillRegisterForm(Browser $browser): Browser
    {
        $emailInput = $browser->resolver->resolveForField('email');
        if ($emailInput->isEnabled()) {
            $browser->type('email', self::EMAIL);
        }

        $browser->type('name', 'Bobby')
            ->check('.c-check-button')
            ->press('Next')
            ->waitForText('Set your password', 20)
            ->type('password', self::PASSWORD)
            ->press('Join');

        return $browser;
    }

    public function register(Browser $browser): Browser
    {
        return $this->fillRegisterForm($browser)
            ->waitForText('How would you like to start?', 20)
            ->pause(500); // Wait for scroll
    }

    public function completePersonalRegistration(Browser $browser): void
    {
        $browser->register()
            ->pause(1000)
            ->press('Next')
            ->waitForText('I want to use Hylark to...', 20)
            ->press('Register without a use')
            ->waitForText('What would you like to do first?', 20);
    }

    public function completeCollabRegistration(Browser $browser): void
    {
        $browser->register()
            ->pause(1000)
            ->tap(fn (Browser $b) => $b->resolver->find('[value="COLLABORATIVE"]')->click())
            ->press('Next')
            ->waitForText('We\'d like to use Hylark to...')
            ->pause(100)
            ->press('Register without a use')
            ->waitForText('What would you like to do first?', 20);
    }
}
