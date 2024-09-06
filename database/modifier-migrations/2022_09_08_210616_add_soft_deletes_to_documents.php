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
        Schema::table('documents', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
        Schema::table('images', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
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
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_by');
        });
        Schema::table('images', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_by');
        });
    }
};
