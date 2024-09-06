<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use Illuminate\Http\Request;
use AccountIntegrations\Core\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

class GoogleCallbackController extends CallbackController
{
    protected function getProvider(): Provider
    {
        return Provider::GOOGLE;
    }

    protected function getScopeMap(): array
    {
        return GoogleRedirectController::SCOPES;
    }

    protected function getOauthProvider(): ProviderInterface
    {
        return Socialite::driver('google');
    }

    protected function getRedirectRoute(): string
    {
        return 'integrations.google.redirect';
    }

    /**
     * @return string[]
     */
    protected function getAcceptedScopes(Request $request): array
    {
        /** @var string $scopes */
        $scopes = $request->query('scope', '');

        return explode(' ', $scopes);
    }
}
