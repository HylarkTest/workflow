<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\MailHog;
use Tests\Browser\Pages\Registration;
use Tests\Browser\Pages\ForgotPassword;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

test('a user can register with a personal base', function () {
    $this->browse(function (Browser $browser, Browser $mailhog) {
        $this->clearEmails();
        $browser->visitAndWait(new Registration)
            ->completePersonalRegistration()
            ->assertSee('Activate your account!');

        $mailhog->visit(new MailHog)
            ->waitFor('@messages')
            ->openLastEmail('Verify Email')
            ->clickOnCallToAction()
            ->whenLoaded()
            ->acceptCookies()
            ->on(new Login)
            ->fillLogin()
            ->waitForText('Thank you for activating your account! Good to go.')
            ->quit();

        $browser->refresh()
            ->whenLoaded()
            ->assertDontSee('Activate your account!');
    });
});

test('a user is redirected to their bootstrap place after activating', function () {
    $this->browse(function (Browser $browser) {
        $this->clearEmails();
        $browser->visitAndWait(new Registration)
            ->register()
            ->pause(1000)
            ->press('Next')
            ->waitForText('I want to use Hylark to...', 20);

        $browser->visit(new MailHog)
            ->waitFor('@messages')
            ->openLastEmail('Verify Email')
            ->clickOnCallToAction()
            ->whenLoaded()
            ->waitForText('Home')
            ->tinker()
            ->clickLink('Home')
            ->waitForText('I want to use Hylark to...', 20);
    });
});

test('if a user logs out before setting their password they can set it on login', function () {
    $this->browse(function (Browser $browser) {
        $this->clearEmails();
        $browser->visitAndWait(new Registration)
            ->register();

        $browser->logout();

        $browser->visitAndWait(new ForgotPassword)
            ->getLink();

        $browser->visit(new MailHog)
            ->waitForText('Password reset request')
            ->openLastEmail('Password reset request')
            ->clickOnCallToAction()
            ->waitForText('Set your new password')
            ->type('password', Registration::PASSWORD)
            ->press('Continue')
            ->waitForText('Back to login')
            ->clickLink('Back to login')
            ->waitForText('Sign in')
            ->on(new Login)
            ->fillLogin()
            ->assertAuthenticated();
    });
});

test('a user can register with a collaborative base', function () {
    $this->browse(function (Browser $browser) {
        $this->clearEmails();
        $browser->visitAndWait(new Registration)
            ->completeCollabRegistration();

        $browser->visit('/horizon')
            ->tinker();
    });
});

test('all the uses work', function () {
    $this->browse(function (Browser $browser) {
        $lastUse = false;

        $useIndex = 0;
        while (! $lastUse) {
            $user = createUser(['finished_registration_at' => null]);

            $browser->logout()->pause(500)
                ->loginAs($user)
                ->visitAndWait('signup');

            $uses = $browser->resolver->all('.o-use-item');
            $use = $uses[$useIndex];
            if (count($uses) === $useIndex + 1) {
                $lastUse = true;
            }
            $useIndex++;
            $use->click();

            $browser->press('Next')
                ->pause(600)
                ->press('Next')
                ->pause(600);

            $path = parse_url($browser->driver->getCurrentURL(), \PHP_URL_PATH) ?? '';
            if ($path === '/signup/refine') {
                $checkboxes = $browser->resolver->all('.c-check-holder');
                foreach ($checkboxes as $index => $checkbox) {
                    if ($checkbox->isDisplayed()) {
                        $browser->scrollTo(".c-check-holder:nth($index)");
                        $checkbox->click();
                    }
                }
                $browser->press('Done');
                $browser->press('Next');
                $browser->pause(600);
            }

            $browser->press('Finish')
                ->waitForText('Welcome', 10);

            $pages = $browser->resolver->all('.o-home-display__page');

            foreach (array_keys($pages) as $index) {
                $child = $index + 1;
                $browser->scrollIntoView(".o-home-display__page:nth-child($child)");
                $browser->click(".o-home-display__page:nth-child($child) a")
                    ->waitUntilMissing('.c-loader-processing')
                    ->pause(3000)
                    ->assertDontSee('An error occurred');

                $browser->click('a[href="/home"]')->waitForText('Welcome');
            }
            $browser->pause(2000);
        }
    });
});
