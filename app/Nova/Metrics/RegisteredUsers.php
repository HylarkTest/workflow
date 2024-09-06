<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use App\Models\CronResult;
use Laravel\Nova\Metrics\Progress;
use Laravel\Nova\Metrics\ProgressResult;

class RegisteredUsers extends Progress
{
    /**
     * Get the displayable name of the metric
     */
    public function name(): string
    {
        return 'Users who have finished registration';
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(): ProgressResult
    {
        $registeredUsers = User::query()->whereNotNull('finished_registration_at')->count();
        $cronResultSum = CronResult::query()->whereNotNull('unfinished_registrations_count')->sum('unfinished_registrations_count');
        $unfinishedRegistrations = User::query()->whereNull('finished_registration_at')->count();

        return new ProgressResult(
            value: $registeredUsers,
            target: $registeredUsers + $cronResultSum + $unfinishedRegistrations,
        );
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): \DateInterval|float|\DateTimeInterface|int
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'registered-users';
    }
}
