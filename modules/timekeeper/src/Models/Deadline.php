<?php

declare(strict_types=1);

namespace Timekeeper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Timekeeper\Database\Factories\DeadlineFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Timekeeper\Models\Collections\DeadlineCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Class Deadline
 *
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property int $order
 * @property int $deadline_group_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Timekeeper\Models\DeadlineGroup $group
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Timekeeper\Models\Deadline>
 */
class Deadline extends Model implements Sortable
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
     * @param  array<array-key, \Timekeeper\Models\Deadline>  $models
     * @return \Timekeeper\Models\Collections\DeadlineCollection
     */
    public function newCollection(array $models = [])
    {
        return new DeadlineCollection($models);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Timekeeper\Models\DeadlineGroup, \Timekeeper\Models\Deadline>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(DeadlineGroup::class, 'deadline_group_id');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Timekeeper\Models\Deadline>  $query
     * @param  \Timekeeper\Models\DeadlineGroup|int  $group
     * @return \Illuminate\Database\Eloquent\Builder<\Timekeeper\Models\Deadline>
     */
    public function scopeFromGroup(Builder $query, $group): Builder
    {
        $id = $group instanceof DeadlineGroup ? $group->getKey() : $group;

        return $query->where('deadline_group_id', $id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Timekeeper\Models\Deadline>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->fromGroup($this->deadline_group_id);
    }

    protected static function newFactory(): DeadlineFactory
    {
        return DeadlineFactory::new();
    }
}
