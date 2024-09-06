<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ActionPerformerProvider
{
    public function getPerformer(): Model;
}
