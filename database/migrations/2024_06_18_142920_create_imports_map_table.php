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
     *
     * @return void
     */
    public function up()
    {
        $this->createTableForDistribution('imports_map', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('import_id');
            $table->unsignedBigInteger('row');
            $table->string('status');
            $table->string('failure_reason')->nullable();
            $table->string('importable_type')->nullable();
            $table->unsignedBigInteger('importable_id')->nullable();
            $table->timestamps();

            if ($this->usingSqliteConnection()) {
                $table->foreign('import_id')->references('id')->on('imports')->cascadeOnDelete();
                $table->index(['importable_type', 'importable_id']);
                $table->index('status');
            } else {
                $table->foreign(['base_id', 'import_id'])->references(['base_id', 'id'])->on('imports')->cascadeOnDelete();
                $table->index(['base_id', 'importable_type', 'importable_id']);
                $table->index(['base_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imports_map');
    }
};
