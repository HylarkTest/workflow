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
        if (! Schema::hasTable('support_folders')) {
            Schema::create('support_folders', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('order');
                $table->foreignId('category_id')->constrained('support_categories')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('support_folders')->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
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
        Schema::dropIfExists('support_folders');
    }
};
