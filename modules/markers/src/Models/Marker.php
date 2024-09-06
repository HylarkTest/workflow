<?php

declare(strict_types=1);

namespace Markers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Markers\Database\Factories\MarkerFactory;
use Markers\Models\Collections\MarkerCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Class Marker
 *
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property int $order
 * @property int $marker_group_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Markers\Models\MarkerGroup $group
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Markers\Models\Marker>
 */
class Marker extends Model implements Sortable
{
    use HasFactory;
    use IsSortable;

    protected $casts = [
        'order' => 'int',
    ];

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'color',
            'order',
        ]);
    }

    /**
     * @param  array<array-key, \Markers\Models\Marker>  $models
     * @return \Markers\Models\Collections\MarkerCollection
     */
    public function newCollection(array $models = [])
    {
        return new MarkerCollection($models);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Markers\Models\MarkerGroup, \Markers\Models\Marker>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(config('markers.models.marker_group'), 'marker_group_id');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Markers\Models\Marker>  $query
     * @param  \Markers\Models\MarkerGroup|int  $group
     * @return \Illuminate\Database\Eloquent\Builder<\Markers\Models\Marker>
     */
    public function scopeFromGroup(Builder $query, $group): Builder
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $query->where('marker_group_id', $id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Markers\Models\Marker>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->fromGroup($this->marker_group_id);
    }

    protected static function newFactory(): MarkerFactory
    {
        return MarkerFactory::new();
    }
}
