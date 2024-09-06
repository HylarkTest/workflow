<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Models\Action;

interface CustomActionProvider
{
    public static function customAction(Action $baseAction): Action;
}
