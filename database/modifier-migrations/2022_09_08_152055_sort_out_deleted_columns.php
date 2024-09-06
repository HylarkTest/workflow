<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('notes', function (Blueprint $table) {
            if (! Schema::hasColumn('notes', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('notes', 'deleted_by')) {
                $table->string('deleted_by')->nullable();
            }
        });

        Schema::table('links', function (Blueprint $table) {
            if (! Schema::hasColumn('links', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('links', 'deleted_by')) {
                $table->string('deleted_by')->nullable();
            }
        });
        Schema::table('pins', function (Blueprint $table) {
            if (! Schema::hasColumn('pins', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('pins', 'deleted_by')) {
                $table->string('deleted_by')->nullable();
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
