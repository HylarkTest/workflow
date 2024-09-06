<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
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
        Artisan::call('notes:format:migrate', [
            'CURRENT_FORMAT_MARKUP' => 'DELTA',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
