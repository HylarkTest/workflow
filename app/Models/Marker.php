<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MarkerFactory;
use Markers\Models\Marker as BaseMarker;
use LighthouseHelpers\Concerns\HasGlobalId;
use App\Models\Concerns\HasBaseScopedRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class MarkerGroup
 *
 * @property \App\Models\MarkerGroup $group
 */
class Marker extends BaseMarker
{
    use HasBaseScopedRelationships;
    use HasGlobalId;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Markable>
     */
    public function emailPivots(): HasMany
    {
        return $this->hasMany(Markable::class)
            ->where('markable_type', 'emails');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Todo>
     */
    public function todos(): MorphToMany
    {
        return $this->morphedByMany(Todo::class, 'markable')
            ->withTimestamps();
    }

    protected static function newFactory(): MarkerFactory
    {
        return MarkerFactory::new();
    }
}
