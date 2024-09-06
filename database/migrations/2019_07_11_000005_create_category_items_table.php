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
        $this->createTableForDistribution('category_items', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->string('name', 255);
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'category_id'])->references(['base_id', 'id'])->on('categories')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_items');
    }
};
