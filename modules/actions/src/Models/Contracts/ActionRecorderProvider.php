<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Core\Contracts\ActionRecorder;

interface ActionRecorderProvider
{
    public static function getActionRecorder(): ?ActionRecorder;
}
