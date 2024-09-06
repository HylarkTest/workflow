<?php

declare(strict_types=1);

namespace Timekeeper\Models;

use Timekeeper\Core\DeadlineType;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Timekeeper\Database\Factories\DeadlineGroupFactory;

/**
 * Class DeadlineGroup
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Deadlines\Core\DeadlineType $type
 *
 * Relationships
 * @property \Deadlines\Models\Collections\DeadlineCollection $deadlines
 */
class DeadlineGroup extends Model
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
            'type' => DeadlineType::class,
        ], $casts);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Deadlines\Models\Deadline>
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }

    public function orderDeadlines(array $orders): void
    {
        $this->deadlines->updateOrder($orders);
    }

    protected static function newFactory(): DeadlineGroupFactory
    {
        return DeadlineGroupFactory::new();
    }
}
