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
        Schema::table('markables', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('markable_type');
            $table->unsignedBigInteger('markable_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markables', function (Blueprint $table) {
            $table->dropColumn('external_id');
            $table->unsignedBigInteger('markable_id')->nullable(false)->change();
        });
    }
};
