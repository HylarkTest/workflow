<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use App\Models\AssigneeGroup;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Assignable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\BaseUserPivot>
     */
    public function assignees(): MorphToMany;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\BaseUserPivot>
     */
    public function assigneesForGroup(AssigneeGroup $group): MorphToMany;
}
