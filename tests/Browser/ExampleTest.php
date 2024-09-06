<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;

test('The site works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visitAndWait('/')
            ->assertSee('Welcome back!');
    });
});
