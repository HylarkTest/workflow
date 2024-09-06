<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        if (Schema::hasColumn('emailables', 'email_created_at')) {
            return;
        }
        Schema::table('emailables', function (Blueprint $table) {
            $table->timestamp('email_created_at', 4)->nullable();
            if ($this->usingSqliteConnection()) {
                $table->index(['email_created_at']);
            }
        });

        if (! $this->usingSqliteConnection()) {
            Schema::table('emailables', function (Blueprint $table) {
                $table->index(['base_id', 'email_created_at']);
            });
        }

        DB::table('emailables')->update(['email_created_at' => DB::raw('created_at')]);

        Schema::table('emailables', function (Blueprint $table) {
            $table->timestamp('email_created_at', 4)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emailables', function (Blueprint $table) {
            $table->dropColumn('email_created_at');
        });
    }
};
