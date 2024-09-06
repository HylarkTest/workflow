<?php

declare(strict_types=1);

use App\Models\Base;
use App\Models\BaseSettings;
use App\Models\UserSettings;
use App\Core\Preferences\BasePreferences;
use App\Core\Preferences\UserPreferences;
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
        UserSettings::query()
            ->eachById(function (UserSettings $row) {
                $settings = $row->getAttributes()['settings'];
                if ($settings) {
                    $settings = json_decode($settings, true, 512, \JSON_THROW_ON_ERROR);
                    if (isset($settings['accentColor'])) {
                        $accentColor = $settings['accentColor'];
                        unset($settings['accentColor']);

                        $base = $row->user->belongsToMany(Base::class)->where('type', 'PERSONAL')->first();

                        if ($base) {
                            $base->settings->updatePreferences(function (BasePreferences $preferences) use ($accentColor) {
                                $preferences->accentColor = $accentColor;
                            });
                        }

                        UserSettings::query()
                            ->where('id', $row->id)
                            ->update([
                                'settings' => json_encode($settings, \JSON_THROW_ON_ERROR),
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
        BaseSettings::query()
            ->eachById(function (BaseSettings $row) {
                $settings = $row->settings->toArray();
                if ($settings) {
                    if (isset($settings['accentColor'])) {
                        $userSettings = $row->base->unscopedBelongsToOne(User::class)
                            ->settings;
                        $userSettings->updatePreferences(function (UserPreferences $preferences) use ($settings) {
                            $preferences->accentColor = $settings['accentColor'];
                        });
                    }
                }
            });

        BaseSettings::truncate();
    }
};
