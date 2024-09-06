<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Models\Action;

interface ModelActionTranslator
{
    public static function getActionDescription(Action $action, bool $withPerformer): string;

    public static function getActionChanges(Action $action): ?array;
}
