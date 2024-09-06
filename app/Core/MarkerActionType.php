<?php

declare(strict_types=1);

namespace App\Core;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;
use App\Core\Actions\MarkerAddedAction;
use App\Core\Actions\MarkerRemovedAction;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType MARKER_ADDED()
 * @method static \Actions\Core\ActionType MARKER_REMOVED()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class MarkerActionType extends Enum
{
    public const MARKER_ADDED = 'MARKER_ADDED';

    public const MARKER_REMOVED = 'MARKER_REMOVED';

    /**
     * @return array<string, class-string>
     */
    public static function customActions(): array
    {
        return [
            self::MARKER_ADDED => MarkerAddedAction::class,
            self::MARKER_REMOVED => MarkerRemovedAction::class,
        ];
    }

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
