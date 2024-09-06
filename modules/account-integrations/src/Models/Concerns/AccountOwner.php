<?php

declare(strict_types=1);

namespace AccountIntegrations\Models\Concerns;

use Illuminate\Support\Collection;
use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property \Illuminate\Database\Eloquent\Collection<int, IntegrationAccount> $integrationAccounts
 */
trait AccountOwner
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\AccountIntegrations\Models\IntegrationAccount>
     */
    public function integrationAccounts(): MorphMany
    {
        return $this->morphMany(IntegrationAccount::class, 'account_owner');
    }

    //    public function allCalendars(): Collection
    //    {
    //        return $this->integrationAccounts->map(fn(IntegrationAccount $account) => $account->allCallendars());
    //    }
}
