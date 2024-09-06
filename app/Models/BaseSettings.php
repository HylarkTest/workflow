<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\NotScoped;
use App\Core\Preferences\BasePreferences;
use App\Models\Concerns\HasSettingsColumn;

/**
 * Attributes
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class BaseSettings extends Model implements NotScoped
{
    /**
     * @use \App\Models\Concerns\HasSettingsColumn<\App\Core\Preferences\BasePreferences>
     */
    use HasSettingsColumn;

    /**
     * @var class-string<\App\Core\Preferences\BasePreferences>
     */
    protected string $settingsClass = BasePreferences::class;

    protected $fillable = [
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
