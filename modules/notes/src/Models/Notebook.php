<?php

declare(strict_types=1);

namespace Notes\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Database\Eloquent\ColorCast;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property \Color\Color|null $color
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<int, \Notes\Models\Note> $notes
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Notes\Models\Notebook>
 */
class Notebook extends Model implements Sortable
{
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Notes\Models\Note>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(config('notes.models.note'));
    }
}
