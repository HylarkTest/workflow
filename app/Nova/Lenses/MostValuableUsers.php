<?php

declare(strict_types=1);

namespace App\Nova\Lenses;

use Laravel\Nova\Nova;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Fields\Avatar;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin \App\Models\User
 */
class MostValuableUsers extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->join('subscriptions', 'subscriptions.user_id', '=', 'users.id')
                ->selectRaw('users.*, subscriptions.name as subscription_name, subscriptions.quantity as subscription_quantity')
                ->where('subscriptions.stripe_status', 'active')
        ), fn ($query) => $query->orderByDesc('subscriptions.quantity'));
    }

    /**
     * Get the fields available to the lens.
     *
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make(Nova::__('ID'), 'id'),

            Avatar::make('Avatar')->disk('images')
                ->resolveUsing(fn ($url) => $url
                    ? (str_starts_with($url, 'base') ? $url : "base{$this->firstPersonalBase()->id}/$url")
                    : null)
                ->preview(fn ($value, $disk) => $value
                    ? Storage::disk($disk)->url($value)
                    : sprintf('/images/defaultPeople/person%d.png', ($this->id % 10) + 1)
                ),
            Text::make('Name', 'name'),
            Text::make('Email'),

            Text::make('Subscription', 'subscription_name'),
            Text::make('Quantity', 'subscription_quantity'),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'most-valuable-users';
    }
}
