<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Preferences\ColorMode;
use App\Core\Preferences\WeekStart;
use App\Core\Preferences\DateFormat;
use App\Core\Preferences\TimeFormat;
use Illuminate\Validation\Rules\Enum;
use App\Core\Preferences\DecimalSeparator;
use Illuminate\Foundation\Http\FormRequest;
use App\Core\Preferences\ThousandsSeparator;
use App\Core\Preferences\NotificationChannel;

class UserPreferencesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'colorMode' => [new Enum(ColorMode::class)],
            'weekdayStart' => [new Enum(WeekStart::class)],
            'timezone' => 'timezone',
            'dateFormat' => [new Enum(DateFormat::class)],
            'timeFormat' => [new Enum(TimeFormat::class)],
            'moneyFormat' => 'array',
            'moneyFormat.decimal' => [new Enum(DecimalSeparator::class)],
            'moneyFormat.separator' => [new Enum(ThousandsSeparator::class)],
            'activeAppNotifications' => 'array',
            'activeAppNotifications.*' => [new Enum(NotificationChannel::class)],
            'lastSeenNotifications' => [],
            'shortcuts' => 'array|max:8',
            'shortcuts.*.id' => 'string',
            'shortcuts.*.type' => 'string|in:PAGE,FEATURE',
        ];
    }
}
