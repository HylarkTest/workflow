<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use App\Core\Imports\ImportItemStatus;
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
        if (Schema::hasColumn('imports_map', 'status')) {
            return;
        }
        Schema::table('imports_map', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->index(['base_id', 'status']);
        });
        DB::table('imports_map')
            ->whereNull('importable_id')
            ->update(['status' => ImportItemStatus::FAILED->value]);
        DB::table('imports_map')
            ->whereNotNull('importable_id')
            ->update(['status' => ImportItemStatus::IMPORTED->value]);

        Schema::table('imports_map', function (Blueprint $table) {
            $table->string('status')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imports_map', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
