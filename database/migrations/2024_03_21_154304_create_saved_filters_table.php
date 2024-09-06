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
        $this->createTableForDistribution('saved_filters', 'base_id', function (Blueprint $table) {
            $table->morphs('filterable');
            $table->unsignedBigInteger('base_user_id')->nullable();
            $table->string('name');
            $this->nullableJsonOrStringColumn($table, 'filters');
            $this->nullableJsonOrStringColumn($table, 'order_by');
            $table->string('group')->nullable();
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
        Schema::dropIfExists('saved_filters');
    }
};
