<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

use Actions\Core\Contracts\ActionRecorder;

trait RecordsActions
{
    public static function getActionRecorder(): ActionRecorder
    {
        return resolve(ActionRecorder::class);
    }
}
