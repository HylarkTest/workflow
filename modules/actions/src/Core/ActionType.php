<?php

declare(strict_types=1);

namespace Actions\Core;

use BenSampo\Enum\Enum;
use LaravelUtils\Enums\ExtendableEnum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * Class ActionType
 *
 * @method static ActionType CREATE()
 * @method static ActionType UPDATE()
 * @method static ActionType DELETE()
 * @method static ActionType RESTORE()
 */
class ActionType extends ExtendableEnum implements LocalizedEnum
{
    public const CREATE = 'CREATE';

    public const UPDATE = 'UPDATE';

    public const DELETE = 'DELETE';

    public const RESTORE = 'RECOVER';

    public static array $customActions = [];

    /**
     * @throws \Exception
     */
    public static function fromEvent(string $eventName): self
    {
        switch ($eventName) {
            case 'created':
                return self::CREATE();
            case 'updated':
                return self::UPDATE();
            case 'deleted':
                return self::DELETE();
            case 'restored':
                return self::RESTORE();
            default:
                throw new \Exception("Event name \"$eventName\" doesn't correspond to an ActionType");
        }
    }

    /**
     * @param  \BenSampo\Enum\Enum<string>|class-string<\BenSampo\Enum\Enum<string>>  $enum
     */
    public static function mergeEnum(Enum|string $enum): void
    {
        parent::mergeEnum($enum);
        if (method_exists($enum, 'customActions')) {
            /** @phpstan-ignore-next-line */
            self::$customActions = array_merge(self::$customActions, $enum::customActions());
        }
    }

    public function isCreate(): bool
    {
        return $this->value === self::CREATE;
    }
}
