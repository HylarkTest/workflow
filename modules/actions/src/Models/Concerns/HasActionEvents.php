<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

use Actions\Core\Contracts\ActionEventManager;

trait HasActionEvents
{
    public static function getActionEventManager(): ActionEventManager
    {
        return resolve(ActionEventManager::class);
    }
}
