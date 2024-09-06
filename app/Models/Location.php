<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\LocationLevel;
use App\Models\Contracts\NotScoped;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 *
 * @property int $id
 * @property int $geoname_id
 * @property int $geoname_parent_id
 * @property int $level
 * @property string $name
 *
 * Accessors
 * @property \App\Core\LocationLevel $locationLevel
 *
 * Relationships
 */
class Location extends Model implements NotScoped
{
    use HasGlobalId;

    protected $primaryKey = 'geoname_id';

    public function getConnectionName()
    {
        return config('mappings.locations.database');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Location>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'geoname_parent_id', 'geoname_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Location, \App\Models\Location>
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(self::class, 'geoname_id', 'geoname_parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function name(): Attribute
    {
        return Attribute::get(function ($name, $attributes = []) {
            if ($attributes['country_code'] ?? false) {
                if ($attributes['country_code'] !== 'US' || $attributes['level'] !== 3) {
                    $name .= " ({$attributes['country_code']})";
                }
            }

            return $name;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<int, void>
     */
    public function id(): Attribute
    {
        return Attribute::get(fn () => $this->geoname_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, void>
     */
    public function locationLevel(): Attribute
    {
        return Attribute::get(fn () => LocationLevel::from($this->level)->name);
    }
}
