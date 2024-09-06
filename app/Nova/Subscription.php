<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Actions\CancelSubscription;
use Pavloniym\ActionButtons\ActionButton;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin \Laravel\Cashier\Subscription
 *
 * @extends \App\Nova\Resource<\Laravel\Cashier\Subscription>
 */
class Subscription extends Resource
{
    /**
     * @var class-string<\Laravel\Cashier\Subscription>
     */
    public static $model = \Laravel\Cashier\Subscription::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query->withoutGlobalScope('base'));
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Owner', null, User::class)->sortable(),

            Text::make('Name'),

            Text::make('Valid', function () {
                return $this->valid() ? 'Yes' : 'No';
            }),

            Number::make('Quantity'),

            Date::make('Ends at'),

            Date::make('Created at'),

            ActionButton::make('')
                ->text('Cancel now!')
                ->styles($this->valid() ? [] : ['display' => 'none'])
                ->action(new CancelSubscription, $this->id),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new CancelSubscription,
        ];
    }
}
