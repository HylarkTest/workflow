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
        if (Schema::hasTable('support_tags')) {
            Schema::rename('support_tags', 'support_topics');
        }

        if (Schema::hasTable('support_article_tag')) {
            Schema::rename('support_article_tag', 'support_article_topic');
            Schema::table('support_article_topic', function (Blueprint $table) {
                $table->renameColumn('tag_id', 'topic_id');
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
        if (Schema::hasTable('support_topics')) {
            Schema::rename('support_topics', 'support_tags');
        }

        if (Schema::hasTable('support_article_topic')) {
            Schema::rename('support_article_topic', 'support_article_tag');
            Schema::table('support_article_tag', function (Blueprint $table) {
                $table->renameColumn('topic_id', 'tag_id');
            });
        }
    }
};
