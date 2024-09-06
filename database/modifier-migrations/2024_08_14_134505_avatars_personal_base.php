<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        $ids = DB::table('base_user')
            ->join('bases', 'bases.id', '=', 'base_user.base_id')
            ->where('bases.type', 'PERSONAL')
            ->pluck('base_user.id');

        DB::table('base_user')
            ->whereIn('base_user.id', $ids)
            ->update(['use_account_avatar' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $ids = DB::table('base_user')
            ->join('bases', 'bases.id', '=', 'base_user.base_id')
            ->where('bases.type', 'PERSONAL')
            ->pluck('base_user.id');

        DB::table('base_user')
            ->whereIn('base_user.id', $ids)
            ->update(['use_account_avatar' => false]);
    }
};
