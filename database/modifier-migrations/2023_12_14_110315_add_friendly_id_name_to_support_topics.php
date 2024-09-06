<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
        Schema::table('support_topics', function (Blueprint $table) {
            $table->string('friendly_id')->nullable()->after('id')->unique();
        });
        DB::table('support_topics')
            ->eachById(function ($topic) {
                DB::table('support_topics')
                    ->where('id', $topic->id)
                    ->update([
                        'friendly_id' => Str::slug($topic->name),
                    ]);
            });

        Schema::table('support_topics', function (Blueprint $table) {
            $table->string('friendly_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_topics', function (Blueprint $table) {
            $table->dropColumn('friendly_id');
        });
    }
};
