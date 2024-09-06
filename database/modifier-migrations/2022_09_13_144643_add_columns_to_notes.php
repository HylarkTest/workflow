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
            $table->timestamp('favorited_at')->nullable()->after('text');
            $table->unsignedSmallInteger('order')->after('text');
            $table->string('name')->nullable()->change();
        });

        Schema::table('pins', function (Blueprint $table) {
            $table->timestamp('favorited_at')->nullable()->after('comments');
            $table->unsignedSmallInteger('order')->after('comments');
            $table->string('name')->nullable()->change();
        });

        Schema::table('links', function (Blueprint $table) {
            $table->timestamp('favorited_at')->nullable()->after('comments');
            $table->unsignedSmallInteger('order')->after('comments');
            $table->string('name')->nullable()->change();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('favorited_at')->nullable()->after('filename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['order', 'favorited_at']);
            $table->string('name')->nullable(false)->change();
        });
    }
};
