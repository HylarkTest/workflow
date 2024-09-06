<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use AccountIntegrations\Core\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

class GoogleRedirectController extends RedirectController
{
    public const SCOPES = [
        'CALENDAR' => [\Google\Service\Calendar::CALENDAR],
        'TODOS' => [\Google\Service\Tasks::TASKS],
        'DOCUMENTS' => [\Google\Service\Drive::DRIVE],
        'EMAILS' => [\Google\Service\Gmail::MAIL_GOOGLE_COM],
        'CONTACTS' => [\Google\Service\PeopleService::CONTACTS],
    ];

    protected function getOauthProvider(): ProviderInterface
    {
        $request = request();
        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver('google');

        return $provider->with([
            'login_hint' => $request->query('renew') ?: $request->user()?->email,
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
        ]);
    }

    protected function getProvider(): Provider
    {
        return Provider::GOOGLE;
    }

    protected function getScopeMap(): array
    {
        return self::SCOPES;
    }

    protected static function getRedirectName(): string
    {
        return 'integrations.google.redirect';
    }
}
