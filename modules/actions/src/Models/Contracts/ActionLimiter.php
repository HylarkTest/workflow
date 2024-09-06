<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ActionLimiter
{
    public function shouldRecordAction(?Model $performer, bool $force): bool;
}
