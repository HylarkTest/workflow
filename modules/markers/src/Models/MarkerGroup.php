<?php

declare(strict_types=1);

namespace Markers\Models;

use Markers\Core\MarkerType;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Markers\Database\Factories\MarkerGroupFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class MarkerGroup
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Markers\Core\MarkerType $type
 *
 * Relationships
 * @property \Markers\Models\Collections\MarkerCollection $markers
 */
class MarkerGroup extends Model
{
    use HasFactory;
    use HasGlobalId;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'type',
            'description',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge([
            'type' => MarkerType::class,
        ], $casts);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Markers\Models\Marker>
     */
    public function markers(): HasMany
    {
        return $this->hasMany(config('markers.models.marker'));
    }

    /**
     * @param  array<int, int|string>  $orders
     */
    public function orderMarkers(array $orders): void
    {
        $this->markers->updateOrder($orders);
    }

    protected static function newFactory(): MarkerGroupFactory
    {
        return MarkerGroupFactory::new();
    }
}
