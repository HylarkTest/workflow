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
     */
    public function up(): void
    {
        $this->createTableForDistribution('spaces', 'base_id', function (Blueprint $table) {
            $table->string('name')->index();
            $this->nullableJsonOrStringColumn($table, 'features');
            $table->string('logo')->nullable();
            $table->string('color')->nullable();
            $this->nullableJsonOrStringColumn($table, 'settings');
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
