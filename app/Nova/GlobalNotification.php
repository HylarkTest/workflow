<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\PushGlobalNotification;
use App\Core\Preferences\NotificationChannel;

/**
 * @mixin \App\Models\GlobalNotification
 *
 * @extends \App\Nova\Resource<\App\Models\GlobalNotification>
 */
class GlobalNotification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\GlobalNotification::class;

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

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Header', 'data->header')
                ->rules('required')
                ->showOnPreview(),
            Text::make('Preview', 'data->preview')
                ->rules(['required']),
            Trix::make('Content', 'data->content')
                ->alwaysShow()
                ->rules('required')
                ->hideFromIndex()
                ->showOnPreview(),
            Image::make('Image', 'data->image')
                ->disk('public')
                ->path('notification-images')
                ->disableDownload()
                ->showOnPreview(),
            Text::make('Link', 'data->link')
                ->showOnPreview(),
            Select::make('Channel')->options([
                NotificationChannel::NEW_FEATURES->value => 'New Feature',
                NotificationChannel::TIPS->value => 'Tips and Tricks',
            ])
                ->resolveUsing(fn (NotificationChannel|string|null $type) => \is_string($type) ? $type : $type?->value)
                ->rules('required')
                ->showOnPreview(),
            DateTime::make('Pushed at')->exceptOnForms()->readonly(),
            DateTime::make('Will be pushed at', 'will_automatically_push_at')->exceptOnForms()->readonly(),
            Boolean::make('Push notification on create', 'pushOnCreate')
                ->help('If checked the notification will be pushed to all
                users when it is created. If unchecked you can push the
                notification at a later date by selecting "Push to all users" in
                the actions menu.')
                ->onlyOnForms()
                ->hideWhenUpdating(),
            DateTime::make('Delay push to', 'delayPushUntil')
                ->help('You can specify a date and time after the
                notification is created for it to be automatically pushed to all
                users. Please note the timezone is UTC which may be different to
                your current timezone.')
                ->rules(['nullable', 'date', 'after:now', 'prohibited_if:pushOnCreate,false'])
                ->onlyOnForms()
                ->hideWhenUpdating(),
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
            (new PushGlobalNotification)
                ->canRun(fn ($request, \App\Models\GlobalNotification $globalNotification) => $globalNotification->pushed_at === null),
        ];
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }
}
