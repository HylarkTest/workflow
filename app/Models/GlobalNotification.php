<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Contracts\NotScoped;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Database\QueryException;
use App\Core\Preferences\NotificationChannel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Attributes
 *
 * @property int $id
 * @property \App\Core\Preferences\NotificationChannel $channel
 * @property array<string, string> $data
 * @property \Illuminate\Support\Carbon|null $pushed_at
 * @property \Illuminate\Support\Carbon|null $will_automatically_push_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\DatabaseNotification> $notifications
 */
class GlobalNotification extends Model implements NotScoped
{
    use HasFactory;

    public bool $pushOnCreate = false;

    public null|string|Carbon $delayPushUntil = null;

    protected $casts = [
        'channel' => NotificationChannel::class,
        'data' => 'array',
        'pushed_at' => 'datetime',
        'will_automatically_push_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DatabaseNotification>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class);
    }

    public function pushToUsers(): void
    {
        $originalTenant = tenancy()->tenant;

        User::query()
            ->with(['bases', 'settings'])
            ->eachById(function (User $user) {
                if (! $user->settings->disabledNotificationType($this->channel)) {
                    tenancy()->initialize($user->firstPersonalBase());
                    try {
                        $user->notifyNow(new \App\Notifications\GlobalNotification($this));
                    } catch (QueryException $exception) {
                        if (Str::contains($exception->getPrevious()?->getMessage() ?: '', 'global_notification_id')) {
                            logger()->error("Could not push notification to user [$user->id]. Notification already exists");
                        }
                    }
                }
            });

        $this->pushed_at = now();
        $this->save();

        if ($originalTenant instanceof Tenant) {
            tenancy()->initialize($originalTenant);
        } else {
            tenancy()->end();
        }
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $notification) {
            if ($delayPushUntil = request()->delayPushUntil) {
                $notification->will_automatically_push_at = Carbon::parse($delayPushUntil);
            }
            $attributes = $notification->getAttributes();
            unset($attributes['delayPushUntil']);
            $notification->setRawAttributes($attributes);
        });

        self::created(function (self $notification) {
            if ($notification->pushOnCreate) {
                dispatch(function () use ($notification) {
                    $notification->pushToUsers();
                })->onConnection('central')->delay($notification->will_automatically_push_at);
            }
        });

        // Global notifications are local tables, not distributed in Citus which
        // means they can't have foreign keys in distributed tables, so we have
        // to manually clean up when they are deleted.
        self::deleted(function (self $notification) {
            $notification->notifications()->withoutGlobalScopes()->delete();
        });
    }
}
