<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

use Actions\Core\Contracts\ActionTranslator;

interface ActionTranslatorProvider
{
    public static function getActionTranslator(): ?ActionTranslator;
}
