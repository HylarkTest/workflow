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
     *
     * @return void
     */
    public function up()
    {
        $this->createTableForDistribution('pages', 'base_id', function (Blueprint $table) {
            $table->string('template_refs')->nullable();
            $table->unsignedBigInteger('space_id');
            $table->unsignedBigInteger('mapping_id')->nullable();

            $table->string('path');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('symbol')->nullable();
            $table->string('image')->nullable();
            $this->nullableJsonOrStringColumn($table, 'design');
            $this->nullableJsonOrStringColumn($table, 'config');
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('space_id')->references('id')->on('spaces')->cascadeOnDelete();
                $table->foreign('mapping_id')->references('id')->on('mappings')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'space_id'])->references(['base_id', 'id'])->on('spaces')->cascadeOnDelete();
                $table->foreign(['base_id', 'mapping_id'])->references(['base_id', 'id'])->on('mappings')->cascadeOnDelete();
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
        Schema::dropIfExists('pages');
    }
};
