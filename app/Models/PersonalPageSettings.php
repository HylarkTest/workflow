<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Core\ActionType;
use Actions\Models\Concerns\HasActions;
use App\Models\Concerns\HasSettingsColumn;
use Actions\Models\Contracts\ActionSubject;
use Illuminate\Contracts\Database\Query\Builder;
use Actions\Models\Contracts\ModelActionRecorder;
use App\Core\Preferences\PersonalPagePreferences;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property-read \App\Models\Page $page
 * @property-read \App\Models\BaseUserPivot $baseUser
 */
class PersonalPageSettings extends Model implements ActionSubject, ModelActionRecorder
{
    use HasActions;

    /**
     * @use \App\Models\Concerns\HasSettingsColumn<\App\Core\Preferences\PersonalPagePreferences>
     */
    use HasSettingsColumn;

    protected array $actionIgnoredColumns = [
        'page_id',
    ];

    /**
     * @var class-string<\App\Core\Preferences\PersonalPagePreferences>
     */
    protected string $settingsClass = PersonalPagePreferences::class;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Page, \App\Models\PersonalPageSettings>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\BaseUserPivot, \App\Models\PersonalPageSettings>
     */
    public function baseUser(): BelongsTo
    {
        return $this->belongsTo(BaseUserPivot::class);
    }

    public function scopeForMember(Builder $query, ?BaseUserPivot $member): Builder
    {
        $member = $member ?? tenant()->pivot;

        return $query->where('base_user_id', $member->getKey());
    }

    public function getActionSubject(\Actions\Models\Action $action): Model
    {
        return $this->page;
    }

    public function getActionType(?BaseModel $performer, ?ActionType $baseType): ActionType
    {
        return ActionType::UPDATE();
    }

    public function getActionPayload(ActionType $type, ?BaseModel $performer): ?array
    {
        /** @var \Actions\Core\ActionRecorder $recorder */
        $recorder = static::getActionRecorder();

        $typeForPayload = $recorder->getType($this);

        return match (true) {
            $typeForPayload->is(ActionType::CREATE()) => [
                'changes' => ['personalSettings' => $this->getAttributes()['settings'] ?? null],
                'original' => [],
            ],
            $typeForPayload->is(ActionType::UPDATE()) => [
                'changes' => ['personalSettings' => $this->getDirty()['settings'] ?? null],
                'original' => ['personalSettings' => $this->getRawOriginal()['settings'] ?? null],
            ],
            default => null,
        };
    }
}
