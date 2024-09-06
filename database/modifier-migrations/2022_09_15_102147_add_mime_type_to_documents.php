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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('mime_type', 16)->nullable()->after('extension');
        });
        Schema::table('images', function (Blueprint $table) {
            $table->string('mime_type', 16)->nullable()->after('extension');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('mime_type');
        });
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('mime_type');
        });
    }
};
