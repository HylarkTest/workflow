<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Emailable;
use App\Models\EmailAddressable;
use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait RelatedToEmails
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Emailable>
     */
    public function emailables(): MorphMany
    {
        return $this->morphMany(Emailable::class, 'emailable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\EmailAddressable>
     */
    public function emailAddressables(): MorphMany
    {
        return $this->morphMany(EmailAddressable::class, 'emailableAddressable', 'emailable_type', 'emailable_id');
    }

    public function getEmailFilterOptions(IntegrationAccount $source): array
    {
        return [
            'addresses' => $this->emailAddressables()
                ->where('integration_account_id', $source->id)
                ->pluck('address')
                ->toArray(),
            'ids' => $this->emailables()
                ->where('integration_account_id', $source->id)
                ->pluck('email_id')
                ->toArray(),
        ];
    }
}
