<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use Illuminate\Http\Request;
use AccountIntegrations\Core\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

class MicrosoftCallbackController extends CallbackController
{
    protected function getProvider(): Provider
    {
        return Provider::MICROSOFT;
    }

    protected function getScopeMap(): array
    {
        return MicrosoftRedirectController::SCOPES;
    }

    protected function getOauthProvider(): ProviderInterface
    {
        return Socialite::driver('azure');
    }

    protected function getRedirectRoute(): string
    {
        return 'integrations.microsoft.redirect';
    }

    /**
     * @return string[]
     */
    protected function getAcceptedScopes(Request $request): array
    {
        return $this->getRequestedScopes();
    }
}
