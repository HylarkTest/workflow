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
        if (! Schema::hasColumn('member_invites', 'deleted_at')) {
            Schema::table('member_invites', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('expires_at');
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
        Schema::table('member_invites', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
