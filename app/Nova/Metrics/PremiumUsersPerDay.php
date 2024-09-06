<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Http\Requests\NovaRequest;

class PremiumUsersPerDay extends Trend
{
    /**
     * Get the displayable name of the metric
     *
     * @return string
     */
    public function name()
    {
        return 'Premium Users';
    }

    /**
     * Calculate the value of the metric.
     *
     * @return \Laravel\Nova\Metrics\TrendResult
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays(
            $request,
            User::whereHas(
                'subscriptions',
                function ($query) {
                    return $query->active();
                }
            )
        )->showSumValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => __('30 Days'),
            60 => __('60 Days'),
            90 => __('90 Days'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'premium-users-per-day';
    }
}
