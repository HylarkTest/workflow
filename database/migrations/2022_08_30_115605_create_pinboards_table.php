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
     *
     * @return void
     */
    public function up()
    {
        $this->createTableForDistribution('pinboards', 'base_id', function (Blueprint $table) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinboards');
    }
};
