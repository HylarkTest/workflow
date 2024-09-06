<?php

declare(strict_types=1);

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
        // Moved to 2024_01_29_074644_change_list_fields_to_be_objects.php to
        // make use of postgres transactions so a failed migration doesn't
        // leave the database in an inconsistent state.
        // Leaving the file here so developers who already ran the migration
        // don't experience any issues.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
