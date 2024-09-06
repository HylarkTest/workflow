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
        $this->createTableForDistribution('emailables', 'base_id', function (Blueprint $table) {
            $table->morphs('emailable');
            $table->foreignId('integration_account_id');
            $table->string('mailbox_id')->nullable();
            $table->string('email_id');
            $table->timestamp('email_created_at');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->index(['email_created_at']);
                $table->index(['mailbox_id', 'email_id']);
                $table->foreign('integration_account_id')->references('id')->on('integration_accounts')->cascadeOnDelete();
            } else {
                $table->index(['base_id', 'email_created_at']);
                $table->index(['base_id', 'mailbox_id', 'email_id']);
                $table->foreign(['base_id', 'integration_account_id'])->references(['base_id', 'id'])->on('integration_accounts')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emailables');
    }
};
