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
        DB::table('pages')
            ->eachById(function ($page) {
                $config = json_decode($page->config ?? '', true);
                if ($config['fieldFilters'] ?? false) {
                    $config['fieldFilters'] = array_map(function ($filter) {
                        return [
                            ...$filter,
                            'match' => json_encode($filter['match'], \JSON_THROW_ON_ERROR),
                        ];
                    }, $config['fieldFilters']);
                }
                DB::table('pages')
                    ->where('id', $page->id)
                    ->update([
                        'config' => json_encode($config, \JSON_THROW_ON_ERROR),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
