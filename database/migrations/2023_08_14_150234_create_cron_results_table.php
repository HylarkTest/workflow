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
        Schema::create('cron_results', static function (Blueprint $table) {
            $table->id();
            $table->smallInteger('unfinished_registrations_count')->unsigned()->default(0); // 0-65535
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_results');
    }
};
