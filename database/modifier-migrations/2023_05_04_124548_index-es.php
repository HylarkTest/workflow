<?php

declare(strict_types=1);

use App\Models\Item;
use Finder\Console\GlobalImport;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\Console\ImportCommand;
use LaravelUtils\Database\KnowsConnection;
use Elastic\Migrations\Console\FreshCommand;
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
        Artisan::call(FreshCommand::class, ['--force' => true]);
        Artisan::call(GlobalImport::class);
        Artisan::call(ImportCommand::class, ['model' => Item::class]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
