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
        $this->createTableForDistribution('notes', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('notebook_id');
            $table->string('name')->nullable();
            $table->text('text');
            $table->timestamp('favorited_at')->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('notebook_id')->references('id')->on('notebooks')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'notebook_id'])->references(['base_id', 'id'])->on('notebooks')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
