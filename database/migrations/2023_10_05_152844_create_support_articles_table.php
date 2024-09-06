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
        if (! Schema::hasTable('support_articles')) {
            Schema::create('support_articles', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('order');
                $table->foreignId('folder_id')->constrained('support_folders')->cascadeOnDelete();
                $table->string('title');
                $table->string('friendly_url')->nullable();
                $table->text('content');
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('thumbs_up')->default(0);
                $table->unsignedInteger('thumbs_down')->default(0);
                $table->foreignId('latest_id')->nullable()->constrained('support_articles')->cascadeOnDelete();
                $table->timestamp('live_at')->nullable();
                $table->string('status')->index();
                $table->string('edited_by')->nullable();
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
        Schema::dropIfExists('support_articles');
    }
};
