<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTableForDistribution('calendars', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('order');
            $table->boolean('is_default')->default(false);
            $table->string('template_refs')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('space_id')->references('id')->on('spaces')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'space_id'])->references(['base_id', 'id'])->on('spaces')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
