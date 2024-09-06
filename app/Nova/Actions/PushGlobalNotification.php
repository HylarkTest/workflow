<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\DateTime;
use App\Models\GlobalNotification;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Http\Requests\NovaRequest;

class PushGlobalNotification extends DestructiveAction
{
    public $name = 'Push to all users';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\GlobalNotification>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $notifications = $models->whereNull('pushed_at');
        if ($fields->delay) {
            $delay = Carbon::parse($fields->delay);
            $notifications->each(function (GlobalNotification $notification) use ($delay) {
                $notification->will_automatically_push_at = $delay;
                $notification->save();
            });
        } else {
            $delay = null;
        }

        dispatch(function () use ($notifications) {
            $notifications->each(function (GlobalNotification $notification) {
                $notification->pushToUsers();
            });
        })->onConnection('central')->delay($delay);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            DateTime::make('Delay push to', 'delay')
                ->help('You can optionally delay the push to a specified date in the future.
                Please note the timezone is UTC which may be different to your current timezone.')
                ->rules(['nullable', 'date', 'after:now']),
        ];
    }
}
