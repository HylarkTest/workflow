<?php

declare(strict_types=1);

namespace Planner\Models;

use Color\Color;
use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Database\Eloquent\ColorCast;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property bool $is_default
 * @property \Color\Color|null $color
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \LaravelUtils\Database\Eloquent\Collections\SortableCollection $todos
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Planner\Models\Calendar>
 */
class Calendar extends Model implements Sortable
{
    use HasFactory;
    use IsSortable;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'is_default',
            'color',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'is_default' => 'boolean',
            'color' => ColorCast::class,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Planner\Models\Event>
     */
    public function events(): HasMany
    {
        return $this->hasMany(config('planner.models.event'), $this->getForeignKey());
    }

    public function colorOrDefault(): Color
    {
        $color = $this->color;
        if (! $color) {
            $defaultHex = config('planner.events.default_color', '#AEAEAE');

            return Color::make($defaultHex);
        }

        return $color;
    }
}
