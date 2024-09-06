<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected array $colorMap = [
        '#381bf3' => 'intenseBlue',
        '#6e1bf3' => 'electricPurple',
        '#a41bf3' => 'electricViolet',
        '#da1bf3' => 'fuchsia',
        '#f31bd6' => 'magenta',
        '#f31b58' => 'amaranth',
        '#f3261b' => 'brightRed',
        '#f36e1b' => 'blazeOrange',
        '#e49712' => 'ochre',
        '#d9c10b' => 'goldTips',
        '#6caa09' => 'lime',
        '#09aa47' => 'chateauGreen',
        '#09aa97' => 'turquoiseGreen',
        '#099ab0' => 'brightTurquoise',
        '#0e9bf2' => 'lochmara',
        '#1b6af3' => 'ribbonBlue',
        '#747c8b' => 'steel',
        '#8b7474' => 'hemp',
    ];

    protected array $tables = [
        'base_settings' => 'settings',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table => $column) {
            DB::table($table)->whereNotNull($column)
                ->select('id', $column)
                ->orderBy('id')
                ->each(function ($row) use ($table, $column) {
                    $settings = $row->{$column};
                    $settings = str_ireplace(
                        array_keys($this->colorMap),
                        array_values($this->colorMap),
                        $settings
                    );
                    DB::table($table)->where('id', $row->id)->update([$column => $settings]);
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table => $column) {
            DB::table($table)->whereNotNull($column)
                ->select('id', $column)
                ->orderBy('id')
                ->each(function ($row) use ($table, $column) {
                    $settings = $row->{$column};
                    $settings = str_replace(
                        array_values($this->colorMap),
                        array_keys($this->colorMap),
                        $settings
                    );
                    DB::table($table)->where('id', $row->id)->update([$column => $settings]);
                });
        }
    }
};
