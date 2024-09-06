<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @property \Illuminate\Database\Eloquent\Builder<\App\Models\User> $apply
 */
class UserState extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query
            ->when(
                $value['premium'],
                function ($query, $value) {
                    return $query->whereHas(
                        'subscriptions',
                        function ($query) {
                            return $query->withoutGlobalScope('base')->active();
                        }
                    );
                }
            )
            ->when(
                $value['registration'],
                function ($query, $value) {
                    return $query->whereNull('finished_registration_at');
                }
            )
            ->when(
                $value['verified'],
                function ($query, $value) {
                    return $query->whereNull('email_verified_at');
                }
            );
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return [
            'Premium' => 'premium',
            'Not registered' => 'registration',
            'Not verfied' => 'verified',
        ];
    }
}
