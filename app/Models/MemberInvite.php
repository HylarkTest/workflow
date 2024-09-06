<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Groups\Role;
use Illuminate\Support\Str;
use App\Core\MemberActionType;
use App\Models\Contracts\NotScoped;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Mail\CollabInvite as CollabInviteMail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Notifications\CollabInvite as CollabInviteNotification;

/**
 * Class Invites
 *
 * @property int $id
 * @property string $email
 * @property \App\Core\Groups\Role $role
 * @property string $token
 * @property \Illuminate\Support\Carbon $accepted_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relations
 * @property \App\Models\Base $base
 * @property \App\Models\User $inviter
 * @property \App\Models\User $invitee
 */
class MemberInvite extends Model implements NotScoped
{
    use SoftDeletes;

    public string $originalToken;

    public static string $pruneAfter = 'P1D';

    protected $fillable = [
        'email',
        'role',
        'token',
        'inviter_id',
        'expires_at',
    ];

    protected $casts = [
        'role' => Role::class,
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Base, \App\Models\MemberInvite>
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\MemberInvite>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\MemberInvite>
     */
    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function sendInvitation(string $token): void
    {
        $this->originalToken = $token;
        $existingUser = $this->existingUser();
        if ($existingUser) {
            $existingUser->notify(new CollabInviteNotification($this));
        } else {
            Mail::to($this->email)->send(new CollabInviteMail($this, false));
        }
    }

    public function existingUser(): ?User
    {
        return User::query()->where('email', ilike(), $this->email)->first();
    }

    public function getInviteLink(): string
    {
        app('url')->forceRootUrl(config('app.url'));

        return route('member-invite.accept', ['invite' => $this, 'token' => $this->originalToken], true);
    }

    public function hasExpired(): bool
    {
        return ! $this->expires_at || $this->expires_at->isPast();
    }

    public function verifyToken(string $token): bool
    {
        return Hash::check($token, $this->token);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function token(): Attribute
    {
        return Attribute::set(fn (string $value) => Hash::make($value));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\MemberInvite>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\MemberInvite>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNotExists(function (\Illuminate\Database\Query\Builder $query) {
            $query->selectRaw('1')
                ->from($this->getTable(), '_t')
                ->whereColumn('_t.base_id', $this->qualifyColumn('base_id'))
                ->whereColumn('_t.email', $this->qualifyColumn('email'))
                ->whereNotNull('_t.accepted_at');
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\MemberInvite>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\MemberInvite>
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_at');
    }

    public static function generateToken(): string
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invite) {
            if (! $invite->expires_at) {
                $invite->expires_at = now()->addDay();
            }
        });

        static::created(function (self $invite) {
            $resent = $invite->base
                ->memberInvites()
                ->whereKeyNot($invite->getKey())
                ->where('email', $invite->email)
                ->exists();

            Action::createAction(
                $invite->base,
                $invite->inviter,
                $resent
                    ? MemberActionType::MEMBER_INVITE_RESENT()
                    : MemberActionType::MEMBER_INVITED(),
                ['email' => $invite->email, 'role' => $invite->role->value],
            );
        });

        static::updating(function (self $invite) {
            if ($invite->isDirty('invitee_id')) {
                $invite->accepted_at = now();
            }
        });

        static::updated(function (self $invite) {
            if ($invite->wasChanged('invitee_id')) {
                $invite->base->run(fn () => Action::createAction(
                    $invite->base,
                    $invite->invitee,
                    MemberActionType::MEMBER_INVITE_ACCEPTED(),
                ));
            }
        });
    }

    public static function createAndSend(Base $base, User $inviter, string $email, Role $role): self
    {
        $token = self::generateToken();

        $invite = $base->memberInvites()->make([
            'role' => $role,
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addDay(),
        ]);

        $invite->inviter()->associate($inviter);
        $invite->save();

        $invite->sendInvitation($token);

        Subscription::broadcast('memberInvited', $invite);

        return $invite;
    }
}
