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
        $files = \Symfony\Component\Finder\Finder::create()
            ->files()
            ->in(__DIR__.'/../modifier-migrations')
            ->getIterator();

        $batch = (DB::table('migrations')->latest('batch')->first('batch')->batch ?? 0) + 1;
        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $migrationName = strtok($file->getBasename(), '.');
            if (DB::table('migrations')->where('migration', $migrationName)->doesntExist()) {
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
