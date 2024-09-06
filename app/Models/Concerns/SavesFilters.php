<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Actions\Models\Action;
use App\Models\SavedFilter;
use App\Models\BaseUserPivot;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Core\Actions\ActionTypes\SavedFilterActionType;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SavesFilters
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\SavedFilter>
     */
    public function savedFilters(): MorphMany
    {
        return $this->morphMany(SavedFilter::class, 'filterable');
    }

    public function canSaveFilters(): bool
    {
        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\SavedFilter>
     */
    public function savedFiltersForUser(BaseUserPivot $user): MorphMany
    {
        return $this->savedFilters()->where('base_user_id', $user->id);
    }

    public function isPrivateAction(Action $action): bool
    {
        return in_array(
            $action->type->value,
            [
                SavedFilterActionType::PRIVATE_SAVED_FILTER_CREATE()->value,
                SavedFilterActionType::PRIVATE_SAVED_FILTER_UPDATE()->value,
                SavedFilterActionType::PRIVATE_SAVED_FILTER_DELETE()->value,
            ],
            true
        );
    }
}
