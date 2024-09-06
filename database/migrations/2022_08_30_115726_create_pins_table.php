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
        $this->createTableForDistribution('pins', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('pinboard_id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->timestamp('favorited_at')->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('pinboard_id')->references('id')->on('pinboards')->cascadeOnDelete();
                $table->foreign('document_id')->references('id')->on('images')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'pinboard_id'])->references(['base_id', 'id'])->on('pinboards')->cascadeOnDelete();
                $table->foreign(['base_id', 'document_id'])->references(['base_id', 'id'])->on('images')->cascadeOnDelete();
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
        Schema::dropIfExists('pins');
    }
};
