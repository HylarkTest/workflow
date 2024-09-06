<?php

declare(strict_types=1);

use App\Models\Base;
use App\Models\Item;
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
        Schema::table('items', function (Blueprint $table) {
            $table->string('name')->after('id')->default('')->index();
        });
        tenancy()->runForMultiple(null, function (Base $base) {
            $base->items()->orderBy('id')
                ->eachById(function (Item $item) {
                    $item->update([
                        'name' => $item->resolvePrimaryName() ?? '',
                    ]);
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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
