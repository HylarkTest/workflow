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
        DB::table('user_settings')
            ->eachById(function ($row) {
                $settings = $row->settings;
                if ($settings) {
                    $settings = json_decode($settings, true);
                    $shortcuts = $settings['shortcuts'] ?? [];
                    if ($shortcuts) {
                        $baseUserSettings = json_encode(['shortcuts' => $shortcuts]);
                        DB::table('base_user')
                            ->where('user_id', $row->user_id)
                            ->update(['settings' => $baseUserSettings]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
