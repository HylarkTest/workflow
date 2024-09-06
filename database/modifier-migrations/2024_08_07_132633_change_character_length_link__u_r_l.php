<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        Schema::table('links', function (Blueprint $table) {
            $table->string('new_url', 2048)->default('')->after('url');
        });
        DB::table('links')->update(['new_url' => DB::raw('url')]);
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('url');
        });
        Schema::table('links', function (Blueprint $table) {
            $table->renameColumn('new_url', 'url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->renameColumn('url', 'new_url');
        });
        Schema::table('links', function (Blueprint $table) {
            $table->string('url', 2048)->default('')->after('url');
        });
        DB::table('links')->update(['url' => DB::raw('new_url')]);
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('new_url');
        });
    }
};
