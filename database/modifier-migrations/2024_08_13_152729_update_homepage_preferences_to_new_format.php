<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\GlobalId\GlobalId;
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
        $globalId = resolve(GlobalId::class);
        DB::table('base_settings')
            ->eachById(function ($baseSettings) use ($globalId) {
                if (! $baseSettings->settings) {
                    return;
                }
                $settings = json_decode($baseSettings->settings, true);
                $pages = $settings['homepage']['pages'] ?? false;
                if ($pages !== false) {
                    unset($settings['homepage']['pages']);
                    if ($pages !== null) {
                        $ids = array_map([$globalId, 'decodeId'], $pages);
                        $pages = DB::table('pages')
                            ->whereIn('id', $ids)
                            ->get(['id', 'space_id'])
                            ->groupBy('space_id')
                            ->mapWithKeys(function ($pages, $spaceId) use ($globalId) {
                                return [$globalId->encode('Space', $spaceId) => [
                                    'pages' => $pages->pluck('id')->map(fn ($id) => $globalId->encode('Page', $id))->toArray()],
                                ];
                            });

                        $settings['homepage']['spaces'] = $pages->toArray();
                        DB::table('base_settings')
                            ->where('id', $baseSettings->id)
                            ->update([
                                'settings' => json_encode($settings),
                            ]);
                    }
                }
            });

        DB::table('base_user')
            ->eachById(function ($userSettings) use ($globalId) {
                if (! $userSettings->settings) {
                    return;
                }
                $settings = json_decode($userSettings->settings, true);
                $pages = $settings['homepage']['pages'] ?? false;
                if ($pages !== false) {
                    unset($settings['homepage']['pages']);
                    if ($pages !== null) {
                        $ids = array_map([$globalId, 'decodeId'], $pages);
                        $pages = DB::table('pages')
                            ->whereIn('id', $ids)
                            ->get(['id', 'space_id'])
                            ->groupBy('space_id')
                            ->mapWithKeys(function ($pages, $spaceId) use ($globalId) {
                                return [$globalId->encode('Space', $spaceId) => [
                                    'pages' => $pages->pluck('id')->map(fn ($id) => $globalId->encode('Page', $id))->toArray()],
                                ];
                            });

                        $settings['homepage']['spaces'] = $pages->toArray();
                        DB::table('base_user')
                            ->where('id', $userSettings->id)
                            ->update([
                                'settings' => json_encode($settings),
                            ]);
                    }
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
        DB::table('base_settings')
            ->eachById(function ($baseSettings) {
                if (! $baseSettings->settings) {
                    return;
                }
                $settings = json_decode($baseSettings->settings, true);
                $spaces = $settings['homepage']['spaces'] ?? false;
                if ($spaces !== false) {
                    unset($settings['homepage']['spaces']);
                    $settings['homepage']['pages'] = collect($spaces)->flatMap(fn ($spaces) => $spaces['pages'])->values()->toArray();
                    DB::table('base_settings')
                        ->where('id', $baseSettings->id)
                        ->update([
                            'settings' => json_encode($settings),
                        ]);
                }
            });

        DB::table('base_user')
            ->eachById(function ($userSettings) {
                if (! $userSettings->settings) {
                    return;
                }
                $settings = json_decode($userSettings->settings, true);
                $spaces = $settings['homepage']['spaces'] ?? false;
                if ($spaces !== false) {
                    unset($settings['homepage']['spaces']);
                    $settings['homepage']['pages'] = collect($spaces)->flatMap(fn ($spaces) => $spaces['pages'])->values()->toArray();
                    DB::table('base_user')
                        ->where('id', $userSettings->id)
                        ->update([
                            'settings' => json_encode($settings),
                        ]);
                }
            });
    }
};
