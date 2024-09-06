<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\NotScoped;
use App\Core\Preferences\UserPreferences;
use App\Models\Concerns\HasSettingsColumn;
use App\Core\Preferences\NotificationChannel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserSettings extends Model implements NotScoped
{
    /**
     * @use \App\Models\Concerns\HasSettingsColumn<\App\Core\Preferences\UserPreferences>
     */
    use HasSettingsColumn;

    /**
     * @var class-string<\App\Core\Preferences\UserPreferences>
     */
    protected string $settingsClass = UserPreferences::class;

    protected $fillable = [
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function disabledNotificationType(NotificationChannel $type): bool
    {
        return ! \in_array($type, $this->settings->activeAppNotifications, true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\UserSettings>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
