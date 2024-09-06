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
        $this->createTableForDistribution('assignables', 'base_id', function (Blueprint $table) {
            $table->morphs('assignable');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->index('member_id');
                $table->foreign('group_id')->references('id')->on('assignee_groups')->cascadeOnDelete();
            } else {
                $table->index(['base_id', 'member_id']);
                $table->foreign(['base_id', 'group_id'])->references(['base_id', 'id'])->on('assignee_groups')->cascadeOnDelete();
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
        Schema::dropIfExists('assignables');
    }
};
