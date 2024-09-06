<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseType;
use Laravel\Nova\Util;
use App\Core\Groups\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Cashier\Billable;
use App\Core\Account\AdminRole;
use App\Models\Concerns\HasImage;
use Laravel\Passport\HasApiTokens;
use App\Events\Auth\MemberAccepted;
use App\Models\Concerns\Notifiable;
use App\Models\Contracts\NotScoped;
use Actions\Models\Concerns\HasActions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notification;
use LighthouseHelpers\Concerns\HasGlobalId;
use Actions\Models\Concerns\PerformsActions;
use Actions\Models\Contracts\ActionPerformer;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Concerns\CanUseOneTimePassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Actions\Models\Concerns\ActionClassProvider;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use AccountIntegrations\Models\Concerns\AccountOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\Contracts\CustomEmailNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;
use LaravelUtils\Database\Eloquent\Concerns\CascadesMorphRelationships;

/**
 * Class User
 *
 * @property int $id
 * @property \App\Core\Account\AdminRole|null $admin_role
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $salt
 * @property string $remember_token
 * @property string $two_factor_secret
 * @property int|null $active_base_id
 * @property string|null $avatar
 * @property string|null $avatarUrl
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $finished_registration_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \App\Models\BaseUserPivot|null $pivot
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Base> $bases
 * @property \App\Models\Base $activeBase
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\LoginAttempt> $loginAttempts
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Space> $invitedSpaces
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Mapping> $invitedMappings
 * @property \App\Models\UserSettings $settings
 */
class User extends Authenticatable implements ActionClassProvider, ActionPerformer, Contracts\CanUseOneTimePassword, MustVerifyEmail, NotScoped
{
    use AccountOwner;
    use AdvancedSoftDeletes;
    use Billable {
        subscriptions as cashierSubscriptions;
    }
    use CanUseOneTimePassword;
    use CascadesMorphRelationships {
        cascadeDeleteMorphRelationships as baseCascadeDeleteMorphRelationships;
    }
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasApiTokens;
    use HasFactory;
    use HasGlobalId;
    use HasImage;
    use Notifiable;
    use PerformsActions;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'finished_registration_at' => 'datetime',
        'admin_role' => AdminRole::class,
    ];

    protected array $cascadeRelationships = [
        'notifications' => ['quick'],
    ];

    protected array $actionIgnoredColumns = [
        'password',
    ];

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

            return $this->firstPersonalBase()->run(fn () => Storage::disk('images')->url($avatar));
        });
    }

    public function baseDisplayName(): string
    {
        if (! $this->pivot || ! ($this->pivot instanceof BaseUserPivot)) {
            $pivot = tenant()->pivot;
        } else {
            $pivot = $this->pivot;
        }

        return $pivot->name ?: $this->name;
    }

    public function baseDisplayAvatar(): ?string
    {
        if (! $this->pivot || ! ($this->pivot instanceof BaseUserPivot)) {
            throw new \RuntimeException('User is not attached to a base');
        }

        if ($this->pivot->use_account_avatar) {
            return $this->avatarUrl;
        }

        return $this->pivot->avatarUrl;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoginAttempt>
     */
    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoginAttempt>
     */
    public function successfulLoginAttempts(): HasMany
    {
        return $this->loginAttempts()->where('succeeded', true);
    }

    public function isSuspiciousRequest(Request $request): bool
    {
        // If this is the first successful login then we cannot treat it as
        // suspicious. This will be the basis for future requests.
        if ($this->successfulLoginAttempts()->doesntExist()) {
            return false;
        }
        $ip = Arr::last($request->ips());
        if ($this->successfulLoginAttempts()->where('ip', $ip)->exists()) {
            return false;
        }
        $recentLogins = $this->successfulLoginAttempts()
            ->where('created_at', '>', now()->subMonth())
            ->get();

        foreach ($recentLogins as $login) {
            if ($login->closeToRequest($request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function email(): Attribute
    {
        return Attribute::set(function (string $email, array $attributes = []): array {
            $toSet = ['email' => $email];
            if ($email !== ($attributes['email'] ?? null)) {
                $toSet['email_verified_at'] = null;
            }

            return $toSet;
        });
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function finishedRegistration(): bool
    {
        return $this->finished_registration_at !== null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Base>
     */
    public function bases(): BelongsToMany
    {
        return $this->belongsToMany(Base::class)
            ->using(BaseUserPivot::class)
            ->withPivot(['id', 'role', 'name', 'avatar', 'use_account_avatar', 'settings'])
            ->orderByDesc('type')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BaseUserPivot>
     */
    public function baseUsers(): HasMany
    {
        return $this->hasMany(BaseUserPivot::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Base>
     */
    public function ownedBases(): BelongsToMany
    {
        return $this->bases()
            ->wherePivot('role', Role::OWNER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Space>
     */
    public function invitedSpaces(): MorphToMany
    {
        return $this->morphedByMany(Space::class, 'domain', 'invites', 'invited_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Mapping>
     */
    public function invitedMappings(): MorphToMany
    {
        return $this->morphedByMany(Mapping::class, 'domain', 'invites', 'invited_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\MemberInvite>
     */
    public function memberInvites(): HasMany
    {
        return $this->hasMany(MemberInvite::class, 'invitee_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserSettings>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class)->withDefault();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function newNotifications()
    {
        return $this->unreadNotifications()
            ->where('created_at', '>', $this->settings->settings->lastSeenNotifications ?: $this->created_at);
    }

    public function stripeName(): ?string
    {
        return $this->name;
    }

    public function stripePhone(): ?string
    {
        return null;
    }

    public function isAdmin(): bool
    {
        return ! app()->environment('production')
            || $this->admin_role?->value
            || $this->hasAdminEmail();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAdminEmail()
            || $this->admin_role?->isSuperAdmin();
    }

    public function hasManagerRole(): bool
    {
        return $this->hasRole(AdminRole::MANAGER());
    }

    public function hasSupportRole(): bool
    {
        return $this->hasRole(AdminRole::SUPPORT());
    }

    public function hasKnowledgeBaseRole(): bool
    {
        return $this->hasRole(AdminRole::KNOWLEDGE_BASE_AGENT());
    }

    public function hasRole(AdminRole $role): bool
    {
        return $this->hasAdminEmail()
            || $this->admin_role?->hasFlag($role);
    }

    public function firstPersonalBase(): Base
    {
        /** @var \App\Models\Base $base */
        $base = $this->bases
            ->where('pivot.role', Role::OWNER)
            ->where('type', BaseType::PERSONAL)
            ->first();

        return $base;
    }

    /**
     * Used for testing
     */
    public function firstSpace(): ?Space
    {
        /** @var \App\Models\Space|null $space */
        $space = $this->firstPersonalBase()->spaces->first();

        return $space;
    }

    public function newNotificationsCount(): int
    {
        return $this->newNotifications()->count();
    }

    public function getActiveBase(): Base
    {
        if ($this->active_base_id) {
            /** @var \App\Models\Base|null $activeBase */
            $activeBase = $this->bases->find($this->active_base_id);
            if ($activeBase) {
                return $activeBase;
            }
        }

        return $this->firstPersonalBase();
    }

    public function setActiveBase(Base $base): void
    {
        if ($base->is($this->firstPersonalBase())) {
            $this->active_base_id = null;
        } else {
            $this->active_base_id = $base->id;
        }
        self::withoutTimestamps(fn () => $this->save());
    }

    public function acceptMemberInvite(MemberInvite $invite): void
    {
        $base = $invite->base;
        $changes = $base->members()->syncWithoutDetaching([$this->id => ['role' => $invite->role]]);
        $invite->invitee()->associate($this);
        $invite->save();
        if ($changes['attached']) {
            event(new MemberAccepted($invite));
        }
    }

    public function ownsPremiumBase(): bool
    {
        return $this->bases
            ->where('pivot.role', Role::OWNER)
            /** @phpstan-ignore-next-line  */
            ->contains(fn (Base $base) => $base->isSubscribed());
    }

    public function isMemberOf(Base $base): bool
    {
        return $this->bases->contains($base);
    }

    public function isOwnerOf(Base $base): bool
    {
        /** @phpstan-ignore-next-line  */
        return $this->bases->contains(fn (Base $b) => $b->is($base) && $b->pivot->role === Role::OWNER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Laravel\Cashier\Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->cashierSubscriptions()->withoutGlobalScope('base');
    }

    public function hasAdminEmail(): bool
    {
        return $this->hasVerifiedEmail()
            && \in_array($this->email, config('hylark.admin_emails'), true);
    }

    public function shouldShowInSentry(): bool
    {
        // For debugging on Sentry it is helpful to see if we can contact
        // the person who triggered it.
        return $this->isAdmin() || Str::endsWith($this->email, '@hylark.com');
    }

    public function getActionClass(): string
    {
        return UndistributedAction::class;
    }

    public function routeNotificationForMail(Notification $notification): string
    {
        if ($notification instanceof CustomEmailNotification) {
            return $notification->getEmailAddress();
        }

        return $this->email;
    }

    protected function cascadeDeleteMorphRelationships(): void
    {
        if (! tenancy()->tenant) {
            $this->firstPersonalBase()->run(fn () => $this->baseCascadeDeleteMorphRelationships());
        } else {
            $this->baseCascadeDeleteMorphRelationships();
        }
    }

    protected static function booted()
    {
        static::updated(function (self $customer) {
            if (
                $customer->hasStripeId()
                && $customer->wasChanged(['name', 'email', 'stripe_id', 'pm_type', 'pm_last_four'])
            ) {
                dispatch(static fn () => $customer->syncStripeCustomerDetails())->onConnection('central');
            }
        });

        // static::deleting(function(self $user) {
        //     if ($user->isForceDeleting() && Util::isNovaRequest(request())) {
        //         tenancy()->initialize($user->firstPersonalBase());
        //     }
        // });

        static::deleted(function (self $user) {
            if (! $user->isForceDeleting()) {
                $user->update(['email' => ((string) $user->deleted_at).$user->email]);
            }
        });

        static::restoring(function (self $user) {
            $user->email = Str::after($user->email, (string) $user->deleted_at);
        });
    }
}
