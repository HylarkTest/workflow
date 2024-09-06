<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Nova\Metrics\Partition;
use Illuminate\Support\Facades\Redis;
use Laravel\Nova\Http\Requests\NovaRequest;

class UsersPerPage extends Partition
{
    public $name = 'Users who have not finished registration in the last 30 days';

    /**
     * Calculate the value of the metric.
     *
     * @return \Laravel\Nova\Metrics\PartitionResult
     */
    public function calculate(NovaRequest $request)
    {
        $pages = collect([]);
        foreach (
            User::where('created_at', '>=', now()->subDays(30))
                ->whereNull('finished_registration_at')
                ->lazy() as $user
        ) {
            $page = Redis::connection(config('key-value-store.store'))
                ->get('store:'.$user->id.':savedRegistrationPage') ?: '';
            $page = json_decode($page);
            preg_match('/\/?signup\/(\w+)(\\|\?.+)?/', $page ?? '', $matches);
            $pages->push(Str::ucfirst($matches[1] ?? $page));
        }

        return $this->result($pages->countBy()->toArray());
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
        return 'users-per-page';
    }
}
