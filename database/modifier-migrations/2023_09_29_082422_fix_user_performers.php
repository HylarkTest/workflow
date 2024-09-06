<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
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
        $query = DB::table('actions as a')
            ->where('a.performer_type', 'users')
            ->join('base_user', function (JoinClause $query) {
                $query->on('a.base_id', '=', 'base_user.base_id')
                    ->on('a.performer_id', '=', 'base_user.user_id');
            });

        $update = [
            'a.performer_type' => 'base_user',
            'a.performer_id' => DB::raw(DB::connection()->getTablePrefix().'base_user.id'),
        ];
        if ($this->usingPostgresConnection()) {
            $query->updateFrom($update);
        } else {
            $query->update($update);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
