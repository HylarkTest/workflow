<?php

declare(strict_types=1);

namespace Timekeeper\Models;

use Timekeeper\Models\Collections\DeadlineCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface DeadlineableModel
{
    /**
     * @return int
     */
    public function getKey();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Timekeeper\Models\Deadline>
     */
    public function deadlines(): MorphToMany;

    public function getDeadlines(): DeadlineCollection;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Timekeeper\Models\Deadline>
     */
    public function deadlinesFromGroup(DeadlineGroup $group): MorphToMany;
}
