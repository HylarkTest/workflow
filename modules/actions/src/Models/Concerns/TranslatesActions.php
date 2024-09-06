<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

use Actions\Models\Action;
use Actions\Core\Contracts\ActionTranslator;

trait TranslatesActions
{
    public static function getActionTranslator(): ActionTranslator
    {
        return resolve(ActionTranslator::class);
    }

    public static function getActionDescription(Action $action, bool $withPerformer): string
    {
        $translator = static::getActionTranslator();

        return $translator->translateDescription($action, $withPerformer);
    }

    public static function getActionChanges(Action $action): ?array
    {
        $translator = static::getActionTranslator();

        if ($action->payload) {
            return $translator->buildChangesFromPayload($action);
        }

        return null;
    }
}
