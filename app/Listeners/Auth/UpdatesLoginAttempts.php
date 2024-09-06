<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use App\Core\IPLocation\Location;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Notifications\Auth\SuspiciousLoginNotification;

class UpdatesLoginAttempts
{
    /**
     * Create the event listener.
     */
    public function __construct(protected Request $request) {}

    /**
     * Handle the event.
     */
    public function handle(Login|Failed $event): void
    {
        if (! ($event->user instanceof User) || $event->user->wasRecentlyCreated) {
            return;
        }

        $succeeded = $event instanceof Login;

        $isSuspicious = $succeeded && ! $event->remember && $event->user->isSuspiciousRequest($this->request);

        $ip = Arr::last($this->request->ips());
        $position = resolve(Location::class)->get($ip);

        /** @var \App\Models\LoginAttempt $loginAttempt */
        $loginAttempt = $event->user->loginAttempts()->save((new LoginAttempt)->forceFill([
            'ip' => $ip,
            'succeeded' => $succeeded,
            'user_agent' => $this->request->userAgent(),
            'lat' => $position ? $position->latitude : null,
            'lon' => $position ? $position->longitude : null,
            'city' => $position ? $position->cityName : null,
            'country' => $position ? $position->countryName : null,
        ]));

        if ($isSuspicious) {
            /** @phpstan-ignore-next-line The types should match */
            tenancy()->runForMultiple([$event->user->firstPersonalBase()], fn () => $event->user->notify(new SuspiciousLoginNotification($loginAttempt)));
        }
    }
}
