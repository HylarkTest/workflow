<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    public function getConnection()
    {
        if (config('app.env') === 'production') {
            return config('hylark.support.database');
        }

        return null;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_categories', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_categories', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
