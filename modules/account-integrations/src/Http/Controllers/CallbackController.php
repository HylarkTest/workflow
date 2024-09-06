<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use AccountIntegrations\Core\Provider;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

abstract class CallbackController extends Controller
{
    /**
     * @var array<int, string>
     */
    protected array $requestedScopes;

    protected ?string $renewId;

    public function __invoke(Request $request): ?RedirectResponse
    {
        $provider = $this->getProvider();

        $scopes = $this->getRequestedScopes();
        $renew = $this->getRenewId();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $error = $request->query('error');
        if ($error === 'access_denied' || $error === 'consent_required') {
            return $this->throwError(trans('errors.oauth.denied', ['provider' => Str::title($provider->name)]));
        }

        $acceptedScopes = $this->getAcceptedScopes($request);

        $missingScopes = collect($scopes)
            ->diff($acceptedScopes)
            ->isNotEmpty();

        if ($missingScopes) {
            return $this->throwError(trans('errors.oauth.scopes'));
        }

        $oauthProvider = $this->getOauthProvider();
        /** @var \SocialiteProviders\Manager\OAuth2\User $oauthUser */
        $oauthUser = $oauthProvider->user();

        if ($renew && $renew !== $oauthUser->getEmail()) {
            return $this->throwError(trans('errors.oauth.renew', ['accountName' => $renew]));
        }

        $account = $user->integrationAccounts()->updateOrCreate(
            [
                'provider' => $provider->value,
                'provider_id' => $oauthUser->getId(),
                'account_name' => $oauthUser->getEmail(),
            ],
            [
                'token' => $oauthUser->token,
                'scopes' => collect($scopes)->map(function (string $scope) {
                    foreach ($this->getScopeMap() as $hylarkScope => $providerScopes) {
                        if (\in_array($scope, $providerScopes, true)) {
                            return $hylarkScope;
                        }
                    }
                    throw new \Exception("Could not find scope $scope");
                })->unique()->values(),
                'refresh_token' => $oauthUser->refreshToken,
                'expires_at' => now()->addSeconds($oauthUser->expiresIn),
            ],
        );

        Subscription::broadcast('accountIntegrated', $account, false);

        echo '<script>window.close()</script>';

        return null;
    }

    /**
     * @return string[]
     */
    protected function getRequestedScopes(): array
    {
        if (! isset($this->requestedScopes)) {
            // Getting instead of pulling so that if there's an error and the
            // user refreshes the page, it still shows the same error, instead
            // of failing here.
            $this->requestedScopes = session()->get($this->getProvider()->name.'_scopes');
        }

        return $this->requestedScopes;
    }

    protected function getRenewId(): ?string
    {
        if (! isset($this->renewId)) {
            $this->renewId = session()->get($this->getProvider()->name.'_renew');
        }

        return $this->renewId;
    }

    abstract protected function getProvider(): Provider;

    /**
     * @return array<string, string[]>
     */
    abstract protected function getScopeMap(): array;

    /**
     * @return string[]
     */
    abstract protected function getAcceptedScopes(Request $request): array;

    abstract protected function getOauthProvider(): ProviderInterface;

    abstract protected function getRedirectRoute(): string;

    protected function throwError(string $message): RedirectResponse
    {
        /** @var array<int, string> $scopes */
        $scopes = $this->getRequestedScopes();
        /** @var string|null $renew */
        $renew = $this->getRenewId();

        $scopeMap = $this->getScopeMap();

        return redirect('/error?'.Arr::query([
            'message' => $message,
            'linkText' => 'Try again',
            'link' => route($this->getRedirectRoute(), [
                'scopes' => array_filter(array_keys($scopeMap), function (string $scope) use ($scopeMap, $scopes) {
                    return collect($scopes)->intersect($scopeMap[$scope])->isNotEmpty();
                }),
                'renew' => $renew,
            ]),
        ]));
    }
}
