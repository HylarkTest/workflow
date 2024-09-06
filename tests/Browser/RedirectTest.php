<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Process;

test('the page is reloaded when there is a new build', function () {
    $this->browse(function (Browser $browser) {
        $user = createUser();
        $browser->loginAs($user);
        $browser->visitAndWait('/')
            ->assertSee('Welcome back');

        $time = microtime(true);
        Process::run("RELEASE=\"$time\" yarn --cwd=\"frontend\" build");

        $browser->clickAndWaitForReload('a[href="/todos"]');
    });
});
