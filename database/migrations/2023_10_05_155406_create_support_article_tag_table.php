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
        if (! Schema::hasTable('support_article_topic')) {
            Schema::create('support_article_topic', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('support_articles')->cascadeOnDelete();
                $table->foreignId('topic_id')->constrained('support_topics')->cascadeOnDelete();
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
        Schema::dropIfExists('support_article_topic');
    }
};
