<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Base;
use App\Events\Auth\MemberLeft;
use App\Events\Auth\OwnerRemoved;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use App\Core\Account\AccountLimits;
use App\Events\Auth\MemberAccepted;
use App\Events\Auth\PasswordUpdated;
use App\Listeners\SendUpgradeEmails;
use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateSubscription;
use Illuminate\Support\Facades\Event;
use App\Events\Billing\AccountDeleted;
use App\Listeners\CancelSubscriptions;
use App\Listeners\SendDowngradeEmails;
use Illuminate\Auth\Events\Registered;
use App\Listeners\Auth\BroadcastLogout;
use App\Listeners\BroadcastMemberAdded;
use Illuminate\Database\Eloquent\Model;
use App\Listeners\BroadcastMemberDeleted;
use Illuminate\Auth\Events\PasswordReset;
use App\Events\Billing\SubscriptionCreated;
use App\Events\Core\ProgressTrackerUpdated;
use App\Listeners\Auth\NotifyOn2faDisabled;
use App\Listeners\Auth\UpdatesLoginAttempts;
use App\Events\Billing\SubscriptionDowngraded;
use App\Listeners\Auth\AcceptMemberInvitation;
use App\Listeners\Auth\NotifyOnPasswordChange;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use App\Listeners\Auth\VerifiesEmailOnPasswordReset;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            UpdatesLoginAttempts::class,
            AcceptMemberInvitation::class,
        ],
        Failed::class => [
            UpdatesLoginAttempts::class,
        ],
        PasswordReset::class => [
            VerifiesEmailOnPasswordReset::class,
            NotifyOnPasswordChange::class,
        ],
        PasswordUpdated::class => [
            NotifyOnPasswordChange::class,
        ],
        TwoFactorAuthenticationDisabled::class => [
            NotifyOn2faDisabled::class,
        ],
        SubscriptionCreated::class => [
            SendUpgradeEmails::class,
        ],
        SubscriptionDowngraded::class => [
            SendDowngradeEmails::class,
        ],
        AccountDeleted::class => [
            CancelSubscriptions::class,
        ],
        'eloquent.deleting: '.Base::class => [
            CancelSubscriptions::class,
        ],
        OwnerRemoved::class => [
            CancelSubscriptions::class,
        ],
        MemberAccepted::class => [
            BroadcastMemberAdded::class,
            UpdateSubscription::class,
        ],
        MemberLeft::class => [
            BroadcastMemberDeleted::class,
            UpdateSubscription::class,
            CancelSubscriptions::class,
        ],
        Logout::class => [
            BroadcastLogout::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(Verified::class, function (Verified $event) {
            /** @var \App\Models\User $user */
            $user = $event->user;
            $user->firstPersonalBase()->run(fn () => Subscription::broadcast('meUpdated', $event->user));
        });

        Event::listen(collect(AccountLimits::$limitedModels)->flatMap(fn (string $modelClass) => [
            "eloquent.created: $modelClass",
            "eloquent.deleted: $modelClass",
            "eloquent.restored: $modelClass",
        ])->all(), function (Model $model) {
            if (isset($model->base)) {
                $base = $model->base;
                $base->run(fn () => Subscription::broadcast('baseUpdated', $base));
            }
        });

        Event::listen(ProgressTrackerUpdated::class, function (ProgressTrackerUpdated $event) {
            Subscription::broadcast('progressTrackerUpdated', $event);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
