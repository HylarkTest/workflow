<?php

declare(strict_types=1);

namespace App\Models\Passport;

use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient
{
    public function skipsAuthorization(): bool
    {
        return $this->getKey() === config('hylark.mobile.client_id');
    }
}
