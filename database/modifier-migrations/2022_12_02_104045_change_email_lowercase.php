<?php

declare(strict_types=1);

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
        // This migration was a mistake but keeping it here so rollbacks succeed
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
