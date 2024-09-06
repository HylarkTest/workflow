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
        $this->createTableForDistribution('images', 'base_id', function (Blueprint $table) {
            $table->string('filename', 1023);
            $table->unsignedInteger('size');
            $table->string('url', 255);
            $table->string('extension', 16);
            $table->string('mime_type', 16)->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
