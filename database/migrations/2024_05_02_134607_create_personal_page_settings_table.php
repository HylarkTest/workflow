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
        $this->createTableForDistribution('personal_page_settings', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('base_user_id');
            $this->nullableJsonOrStringColumn($table, 'settings');
            $table->timestamps();
        });
        if ($this->citusInstalled()) {
            $this->createDistributedTable('personal_page_settings', 'base_id');
        }
        Schema::table('personal_page_settings', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->foreign('page_id')->references('id')->on('pages')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'page_id'])->references(['base_id', 'id'])->on('pages')->cascadeOnDelete();
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
        Schema::dropIfExists('personal_page_settings');
    }
};
