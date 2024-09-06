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
        $this->createTableForDistribution('documents', 'base_id', function (Blueprint $table) {
            $table->string('filename', 1023);
            $table->unsignedBigInteger('drive_id')->nullable();
            $table->unsignedInteger('size');
            $table->string('url', 255);
            $table->string('extension', 16);
            $table->string('mime_type', 128)->nullable();
            $table->timestamp('favorited_at')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->foreign('drive_id')->references('id')->on('drives')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'drive_id'])->references(['base_id', 'id'])->on('drives')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
