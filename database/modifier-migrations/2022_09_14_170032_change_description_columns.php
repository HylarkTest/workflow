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
        Schema::table('pins', fn (Blueprint $table) => $table->renameColumn('comments', 'description'));
        Schema::table('links', fn (Blueprint $table) => $table->renameColumn('comments', 'description'));
        Schema::table('pins', fn (Blueprint $table) => $table->text('description')->nullable(true)->change());
        Schema::table('links', fn (Blueprint $table) => $table->text('description')->nullable(true)->change());

        Schema::table('pins', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->dropForeign(['document_id']);
            } else {
                $table->dropForeign(['base_id', 'document_id']);
            }
        });
        Schema::table('pins', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->foreign('document_id')->references('id')->on('images')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'document_id'])->references(['base_id', 'id'])->on('images')->cascadeOnDelete();
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
        Schema::table('pins', fn (Blueprint $table) => $table->renameColumn('description', 'comments'));
        Schema::table('links', fn (Blueprint $table) => $table->renameColumn('description', 'comments'));
    }
};
