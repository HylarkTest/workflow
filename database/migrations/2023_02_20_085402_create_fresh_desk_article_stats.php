<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fresh_desk_article_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('thumbs_up')->default(0);
            $table->unsignedInteger('thumbs_down')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fresh_desk_article_stats');
    }
};
