<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Laravel\Cashier\Subscription;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Actions\DestructiveAction;
use Illuminate\Database\Eloquent\Collection;

class CancelSubscription extends DestructiveAction
{
    public $name = 'Cancel now!';

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Laravel\Cashier\Subscription>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (Subscription $subscription) {
            $subscription->prorate()->cancelNow();
        });
    }
}
