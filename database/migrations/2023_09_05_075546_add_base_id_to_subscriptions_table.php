<?php

declare(strict_types=1);

use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Base::class)->nullable()->after('id')->constrained('bases')->cascadeOnDelete();
        });

        Subscription::query()
            ->eachById(function (Subscription $subscription) {
                if (! $subscription->user) {
                    $subscription->delete();
                } else {
                    $subscription->update([
                        'base_id' => $subscription->user->firstPersonalBase()->id,
                    ]);
                }
            });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Base::class)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('base_id');
        });
    }
};
