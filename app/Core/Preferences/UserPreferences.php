<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class UserPreferences implements Arrayable
{
    public ColorMode $colorMode = ColorMode::LIGHT;

    public ?CarbonTimeZone $timezone = null;

    public WeekStart $weekdayStart = WeekStart::MONDAY;

    public TimeFormat $timeFormat = TimeFormat::TWELVE_HOUR;

    public DateFormat $dateFormat = DateFormat::DAY_MONTH_YEAR;

    /**
     * @var array{ decimal: \App\Core\Preferences\DecimalSeparator, separator: \App\Core\Preferences\ThousandsSeparator }
     */
    public array $moneyFormat = [
        'decimal' => DecimalSeparator::DOT,
        'separator' => ThousandsSeparator::COMMA,
    ];

    /**
     * @var \App\Core\Preferences\NotificationChannel[]
     */
    public array $activeAppNotifications;

    public ?Carbon $lastSeenNotifications;

    /**
     * @param  array{
     *     colorMode?: string,
     *     timezone?: string,
     *     weekdayStart?: int,
     *     timeFormat?: string,
     *     moneyFormat?: array{ decimal: string, separator: string },
     *     dateFormat?: string,
     *     activeAppNotifications?: string[],
     *     lastSeenNotifications?: string
     * }  $preferences
     */
    public function __construct(array $preferences = [])
    {
        if (isset($preferences['colorMode'])) {
            $this->colorMode = ColorMode::from($preferences['colorMode']);
        }

        if (isset($preferences['moneyFormat'])) {
            $this->moneyFormat = [
                'decimal' => DecimalSeparator::from($preferences['moneyFormat']['decimal']),
                'separator' => ThousandsSeparator::from($preferences['moneyFormat']['separator']),
            ];
        }

        if (isset($preferences['weekdayStart'])) {
            $this->weekdayStart = WeekStart::from($preferences['weekdayStart']);
        }

        if (isset($preferences['timezone'])) {
            $this->timezone = CarbonTimeZone::create($preferences['timezone']) ?: null;
        }

        if (isset($preferences['timeFormat'])) {
            $this->timeFormat = TimeFormat::from($preferences['timeFormat']);
        }

        if (isset($preferences['dateFormat'])) {
            $this->dateFormat = DateFormat::from($preferences['dateFormat']);
        }

        if (isset($preferences['activeAppNotifications'])) {
            $this->activeAppNotifications = array_map(
                fn (string $type): NotificationChannel => NotificationChannel::from($type),
                $preferences['activeAppNotifications']
            );
            $this->activeAppNotifications[] = NotificationChannel::ACCOUNT;
        } else {
            $this->activeAppNotifications = NotificationChannel::cases();
        }

        if (isset($preferences['lastSeenNotifications'])) {
            $this->lastSeenNotifications = Carbon::parse($preferences['lastSeenNotifications']);
        } else {
            $this->lastSeenNotifications = null;
        }
    }

    public function toArray(): array
    {
        return [
            'colorMode' => $this->colorMode->value,
            'weekdayStart' => $this->weekdayStart->value,
            'timezone' => $this->timezone?->getName(),
            'timeFormat' => $this->timeFormat->value,
            'dateFormat' => $this->dateFormat->value,
            'moneyFormat' => [
                'decimal' => $this->moneyFormat['decimal']->value,
                'separator' => $this->moneyFormat['separator']->value,
            ],
            'activeAppNotifications' => collect($this->activeAppNotifications)
                ->filter(fn (NotificationChannel $type): bool => $type !== NotificationChannel::ACCOUNT)
                ->values()
                ->map(fn (NotificationChannel $type) => $type->value)
                ->all(),
            'lastSeenNotifications' => $this->lastSeenNotifications?->toJSON(),
        ];
    }
}
