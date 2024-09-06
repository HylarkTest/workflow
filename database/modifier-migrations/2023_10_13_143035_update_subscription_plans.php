<?php

declare(strict_types=1);

use Laravel\Cashier\Subscription;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Base::query()
            ->withWhereHas('subscriptions')
            ->each(function (App\Models\Base $base) {
                $base->subscriptions
                    ->each(function (Subscription $subscription) use ($base) {
                        $subscription->update(['name' => $base->isPersonal() ? 'ascend' : 'soar']);
                    });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Subscription::query()
            ->each(function (Subscription $subscription) {
                $subscription->update(['name' => 'early']);
            });
    }
};
