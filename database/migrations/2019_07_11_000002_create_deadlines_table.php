<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use \LaravelUtils\Database\KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTableForDistribution('deadlines', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('deadline_group_id')->nullable();
            $table->string('name', 255);
            $table->string('color', 31)->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            if ($this->usingSqliteConnection()) {
                $table->foreign('deadline_group_id')->references('id')->on('deadline_groups')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'deadline_group_id'])->references(['base_id', 'id'])->on('deadline_groups')->cascadeOnDelete();
            }
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadlines');
    }
};
