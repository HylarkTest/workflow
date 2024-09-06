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
     */
    public function up(): void
    {
        Schema::table('pages', static function (Blueprint $table) {
            $table->string('image')->nullable()->after('symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', static function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
