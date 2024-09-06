<?php

declare(strict_types=1);

use CitusLaravel\CitusHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTableForDistribution('external_eventables', 'base_id', function (Blueprint $table) {
            $table->morphs('eventable');
            $table->foreignId('integration_account_id');
            $table->string('calendar_id');
            $table->string('event_id');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->index(['calendar_id', 'event_id']);
                $table->foreign('integration_account_id')->references('id')->on('integration_accounts')->cascadeOnDelete();
            }
        });

        if ($this->citusInstalled()) {
            // $this->createDistributedTable('external_eventables', 'base_id');
        }

        if (! $this->usingSqliteConnection()) {
            Schema::table('external_eventables', function (Blueprint $table) {
                $table->index(['base_id', 'calendar_id', 'event_id']);
                $table->foreign(['base_id', 'integration_account_id'])->references(['base_id', 'id'])->on('integration_accounts')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_eventables');
    }
};
