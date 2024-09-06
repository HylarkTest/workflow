<?php

declare(strict_types=1);

namespace App\Core;

use Laravel\Fortify\Fortify;
use Illuminate\Auth\EloquentUserProvider as BaseEloquentUserProvider;

class EloquentUserProvider extends BaseEloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        /** @phpstan-ignore-next-line */
        return $this->newModelQuery()
            ->where(Fortify::username(), ilike(), $credentials[Fortify::username()])
            ->first();
    }
}
