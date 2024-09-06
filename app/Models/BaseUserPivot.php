<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Groups\Role;
use Actions\Core\ActionType;
use App\Core\MemberActionType;
use App\Models\Concerns\HasImage;
use App\Models\Contracts\NotScoped;
use Actions\Models\Concerns\HasActions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Core\Actions\PrivateActionSubject;
use App\Models\Concerns\HasSettingsColumn;
use LighthouseHelpers\Concerns\HasGlobalId;
use Actions\Models\Concerns\PerformsActions;
use Actions\Models\Contracts\ActionPerformer;
use App\Core\Preferences\BaseUserPreferences;
use Actions\Models\Contracts\ModelActionRecorder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $avatar
 * @property string|null $avatarUrl
 * @property bool $use_account_avatar
 * @property \App\Core\Groups\Role $role
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relations
 * @property \App\Models\Base|\App\Models\User $pivotParent
 * @property \App\Models\User $user
 */
class BaseUserPivot extends Pivot implements ActionPerformer, ModelActionRecorder, NotScoped, PrivateActionSubject
{
    use HasActions {
        HasActions::shouldRecordAction as traitShouldRecordAction;
    }
    use HasGlobalId;
    use HasImage;

    /**
     * @use \App\Models\Concerns\HasSettingsColumn<\App\Core\Preferences\BaseUserPreferences>
     */
    use HasSettingsColumn;

    use PerformsActions;

    /**
     * @var class-string<\App\Core\Preferences\BaseUserPreferences>
     */
    protected string $settingsClass = BaseUserPreferences::class;

    protected $table = 'base_user';

    protected $casts = [
        'settings' => 'array',
        'role' => Role::class,
        'use_account_avatar' => 'boolean',
    ];

    protected $with = [
        'user',
    ];

    public function typeName(): string
    {
        return 'Member';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Base, \App\Models\BaseUserPivot>
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\BaseUserPivot>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, never>
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $avatar = $this->avatar;
            if (! $avatar) {
                return null;
            }
            if (filter_var($avatar, \FILTER_VALIDATE_URL)) {
                return $avatar;
            }

            return $this->getBase()->run(fn () => Storage::disk('images')->url($avatar));
        });
    }

    public function getBase(): Base
    {
        /** @var \App\Models\Base $base */
        $base = $this->pivotParent instanceof Base ? $this->pivotParent : $this->base;

        return $base;
    }

    public function displayName(): string
    {
        return $this->name ?: $this->user->name;
    }

    public function displayAvatar(): ?string
    {
        if ($this->use_account_avatar) {
            return $this->user->avatarUrl;
        }

        return $this->avatarUrl;
    }

    public function getActionPerformerName(): string
    {
        return $this->displayName();
    }

    public function shouldRecordAction(?Model $performer, bool $force): bool
    {
        if (! $this->exists || $this->wasRecentlyCreated) {
            return false;
        }

        return $this->traitShouldRecordAction($performer, $force);
    }

    public function isPrivateAction(Action $action): bool
    {
        return $action->type->isNot(MemberActionType::MEMBER_ROLE_UPDATED());
    }

    public function getActionType(?Model $performer, ?ActionType $baseType): ActionType
    {
        return $this->wasChanged('role')
            ? MemberActionType::MEMBER_ROLE_UPDATED()
            : ActionType::UPDATE();
    }

    public function getActionPayload(ActionType $type, ?Model $performer): ?array
    {
        /** @var \Actions\Core\ActionRecorder $recorder */
        $recorder = static::getActionRecorder();

        if ($type->is(MemberActionType::MEMBER_ROLE_UPDATED())) {
            $type = ActionType::UPDATE();
        }

        return $recorder->getPayload($this, $type, $performer);
    }

    public static function formatSettingsActionPayload(): ?array
    {
        return null;
    }

    public static function formatAvatarActionPayload(): ?string
    {
        return null;
    }

    public static function formatRoleActionPayload(string $payload): ?string
    {
        return trans("actions::description.base_user_pivot.payload.role.$payload");
    }
}
