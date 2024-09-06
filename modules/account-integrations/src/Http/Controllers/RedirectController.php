<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use AccountIntegrations\Core\Scope;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\RedirectResponse;
use AccountIntegrations\Core\Provider;
use Illuminate\Validation\ValidationException;
use AccountIntegrations\Models\IntegrationAccount;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

abstract class RedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $request->query('renew') && ! $user->firstPersonalBase()->accountLimits()->canAddIntegrations()) {
            throw ValidationException::withMessages(['limit' => trans('validation.exceeded')]);
        }

        $scopeMap = $this->getScopeMap();
        /** @var array<int, string> $scopes */
        $scopes = $request->validate([
            'scopes' => ['array', Rule::in(array_keys($scopeMap))],
        ])['scopes'] ?? array_keys($scopeMap);

        $scopes = collect($scopes)->flatMap(fn (string $scope): array => $scopeMap[$scope])->all();

        $provider = $this->getProvider();

        session([
            "{$provider->name}_scopes" => $scopes,
            "{$provider->name}_renew" => $request->query('renew'),
        ]);

        /** @phpstan-ignore-next-line We know the scopes method exists */
        return $this->getOauthProvider()
            ->scopes($scopes)
            ->redirect();
    }

    public static function generateRedirectUrl(IntegrationAccount $account): string
    {
        URL::forceRootUrl(config('app.url'));

        return route(static::getRedirectName(), [
            'scopes' => array_map(fn (Scope $scope) => $scope->value, $account->scopes),
            'renew' => $account->account_name,
        ]);
    }

    abstract protected function getOauthProvider(): ProviderInterface;

    abstract protected function getProvider(): Provider;

    /**
     * @return array<string, string[]>
     */
    abstract protected function getScopeMap(): array;

    abstract protected static function getRedirectName(): string;
}
