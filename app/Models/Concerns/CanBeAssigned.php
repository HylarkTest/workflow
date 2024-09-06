<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\AssigneeGroup;
use App\Models\BaseUserPivot;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\BaseUserPivot> $assignees
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CanBeAssigned
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\BaseUserPivot>
     */
    public function assignees(): MorphToMany
    {
        return $this->morphToMany(
            BaseUserPivot::class,
            'assignable',
            'assignables',
            'assignable_id',
            'member_id'
        )->withPivot('group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\BaseUserPivot>
     */
    public function assigneesForGroup(AssigneeGroup $group): MorphToMany
    {
        return $this->assignees()
            ->wherePivot('group_id', $group->id);
    }

    public function getAssigneesMappedForFinder(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->assignees->map(function (BaseUserPivot $assignee) {
            /** @phpstan-ignore-next-line */
            $groupId = $assignee->pivot->group_id;
            $groupGlobalId = resolve(GlobalId::class)->encode((new AssigneeGroup)->typeName(), $groupId);

            return [
                'text' => $assignee->name ?? $assignee->user->name,
                'map' => "assigneeGroups.$groupGlobalId.assignees.{$assignee->global_id}.name",
            ];
        })->toArray();
    }
}
