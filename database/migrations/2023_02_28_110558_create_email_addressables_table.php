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
        $this->createTableForDistribution('email_addressables', 'base_id', function (Blueprint $table) {
            $table->morphs('emailable');
            $table->foreignId('integration_account_id');
            $table->string('mailbox_id')->nullable();
            $table->string('address');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->index(['address']);
                $table->foreign('integration_account_id')->references('id')->on('integration_accounts')->cascadeOnDelete();
            }
        });

        if ($this->citusInstalled()) {
            // $this->createDistributedTable('email_addressables', 'base_id');
        }
        if (! $this->usingSqliteConnection()) {
            Schema::table('email_addressables', function (Blueprint $table) {
                $table->index(['base_id', 'address']);
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
        Schema::dropIfExists('email_addressables');
    }
};
