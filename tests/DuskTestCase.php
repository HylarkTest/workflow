<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\MailHog;
use Illuminate\Support\Facades\Redis;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    public function clearEmails(): void
    {
        (new MailHog)->clearEmails();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled()
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED'])
               || isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    protected function setUp(): void
    {
        Browser::macro('logout', function () {
            return $this->deleteCookie(config('session.cookie'));
        });
        Browser::macro('whenLoaded', function () {
            return $this->waitUntilMissing('.c-loader-main', 30);
        });
        Browser::macro('acceptCookies', function () {
            if ($this->element('.o-cookie-banner')) {
                $this->press('Accept all cookies');
            }

            return $this;
        });
        Browser::macro('visitAndWait', function ($url) {
            return $this->visit($url)->whenLoaded()->acceptCookies();
        });

        parent::setUp();

        Redis::connection()->command('flushdb');
    }
}
