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
        Schema::table('emailables', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->dropIndex(['email_id']);
            }
        });
        Schema::table('emailables', function (Blueprint $table) {
            $table->foreignId('integration_account_id')->after('emailable_id');
            $table->string('mailbox_id');
            if ($this->usingSqliteConnection()) {
                $table->index(['mailbox_id', 'email_id']);
                $table->foreign('integration_account_id')->references('id')->on('integration_accounts')->cascadeOnDelete();
            }
        });

        if ($this->citusInstalled()) {
            $this->createDistributedTable('emailables', 'base_id');
            Schema::table('emailables', function (Blueprint $table) {
                $table->index(['base_id', 'mailbox_id', 'email_id']);
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
        Schema::table('emailables', function (Blueprint $table) {
            $table->dropColumn('integration_account_id');
            $table->dropColumn('mailbox_id');
        });
    }
};
