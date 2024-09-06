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
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lon');
            $table->dropColumn('city');
            $table->dropColumn('country');
        });
    }
};
