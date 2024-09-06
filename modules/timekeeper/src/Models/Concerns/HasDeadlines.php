<?php

declare(strict_types=1);

namespace Timekeeper\Models\Concerns;

use Timekeeper\Models\Deadline;
use Timekeeper\Models\DeadlineGroup;
use Timekeeper\Models\Collections\DeadlineCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Concerns\HasAdvancedRelationships;

/**
 * Trait HasDeadlines
 *
 * @property \Timekeeper\Models\Collections\DeadlineCollection $deadlines
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasDeadlines
{
    use HasAdvancedRelationships;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Timekeeper\Models\Deadline>
     */
    public function deadlines(): MorphToMany
    {
        return $this->morphToMany(Deadline::class, 'deadlinable')
            ->withPivot('created_at as added_at')
            ->withTimestamps();
    }

    public function getDeadlines(): DeadlineCollection
    {
        return $this->deadlines;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Timekeeper\Models\Deadline>
     */
    public function deadlinesFromGroup(int|DeadlineGroup $group): MorphToMany
    {
        $id = $group instanceof DeadlineGroup ? $group->getKey() : $group;

        return $this->deadlines()->where('deadline_group_id', $id);
    }
}
