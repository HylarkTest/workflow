<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Core\ActionType;
use Illuminate\Database\Eloquent\Model;

interface ModelActionRecorder
{
    public function getActionType(?Model $performer, ?ActionType $baseType): ActionType;

    public function getActionPayload(ActionType $type, ?Model $performer): ?array;
}
