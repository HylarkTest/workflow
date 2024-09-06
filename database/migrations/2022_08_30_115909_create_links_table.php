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
        $this->createTableForDistribution('links', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('link_list_id');
            $table->string('name')->nullable();
            $table->string('url', 2048);
            $table->text('description')->nullable();
            $table->timestamp('favorited_at')->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('link_list_id')->references('id')->on('link_lists')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'link_list_id'])->references(['base_id', 'id'])->on('link_lists')->cascadeOnDelete();
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
        Schema::dropIfExists('links');
    }
};
