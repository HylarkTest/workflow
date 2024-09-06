<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTableForDistribution('items', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('mapping_id');
            //            $table->string('type', 255);
            $table->string('name');
            $this->jsonOrStringColumn($table, 'data', 'mediumText');
            $table->timestamp('favorited_at')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('due_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            //            $table->index('type');
            if ($this->usingSqliteConnection()) {
                $table->foreign('mapping_id')->references('id')->on('mappings')->onDelete('CASCADE');
            } else {
                $table->foreign(['base_id', 'mapping_id'])->references(['base_id', 'id'])->on('mappings')->onDelete('CASCADE');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
