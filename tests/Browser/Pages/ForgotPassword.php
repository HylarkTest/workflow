<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ForgotPassword extends Page
{
    public function url(): string
    {
        return '/access/reset';
    }

    public function getLink(Browser $browser): void
    {
        $browser->type('email', Registration::EMAIL)
            ->press('Submit')
            ->waitForText('Thanks!');
    }
}
