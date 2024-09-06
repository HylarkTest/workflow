<?php

declare(strict_types=1);

use App\Models\Image;
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
        DB::table('actions')
            ->where('subject_type', Image::class)
            ->update(['subject_type' => (new Image)->getTable()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
