<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        Schema::table('category_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('order')->nullable()->after('name');
        });

        DB::table('category_items')
            ->groupBy('category_id')
            ->pluck('category_id')
            ->each(function (int $categoryId) {
                $count = 1;
                DB::table('category_items')
                    ->where('category_id', $categoryId)
                    ->pluck('id')
                    ->each(function ($id) use (&$count) {
                        DB::table('category_items')
                            ->where('id', $id)
                            ->update(['order' => $count]);
                        $count++;
                    });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_items', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
