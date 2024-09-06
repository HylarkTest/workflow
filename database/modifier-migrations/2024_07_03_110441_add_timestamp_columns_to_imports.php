<?php

declare(strict_types=1);

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
        Schema::table('imports', function (Blueprint $table) {
            if (Schema::hasColumns('imports', ['cancelled_at', 'reverted_at', 'revert_finished_at'])) {
                return;
            }
            $table->timestamp('revert_finished_at')->nullable()->after('finished_at');
            $table->timestamp('reverted_at')->nullable()->after('finished_at');
            $table->timestamp('cancelled_at')->nullable()->after('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
            $table->dropColumn('reverted_at');
            $table->dropColumn('revert_finished_at');
        });
    }
};
