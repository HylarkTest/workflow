<?php

declare(strict_types=1);

namespace App\Console\Commands\Once;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\FreshDeskArticleStat;
use App\Models\Support\ArticleStatus;
use App\Models\Support\SupportArticle;
use Illuminate\Support\Facades\Storage;
use App\Core\Support\CacheSupportRepository;

class FreshDeskMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:freshdesk:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate FreshDesk articles to Hylark';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::usingConnection(config('hylark.support.database'), function () {
            DB::connection()->getSchemaBuilder()->disableForeignKeyConstraints();
            $imageDisk = Storage::disk('resources');

            DB::table('support_categories')->truncate();
            DB::table('support_articles')->truncate();
            DB::table('support_topics')->truncate();
            DB::table('support_article_topic')->truncate();

            $timestamps = [
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $repository = app(CacheSupportRepository::class);

            $categories = $repository->getSupportCategories();

            $articleMap = [];

            $categoryOrder = 1;
            $folderOrder = 1;
            $articleOrder = 1;
            foreach ($categories as $category) {
                $categoryId = DB::table('support_categories')->insertGetId([
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'order' => $categoryOrder++,
                    ...$timestamps,
                ]);

                foreach ($category['folders'] ?? [] as $folder) {
                    $folderId = DB::table('support_folders')->insertGetId([
                        'name' => $folder['name'],
                        'category_id' => $categoryId,
                        'order' => $folderOrder++,
                        ...$timestamps,
                    ]);
                    foreach ($folder['articles'] ?? [] as $article) {
                        $fullArticle = $repository->getArticle((string) $article['id']);

                        $content = $fullArticle['description'] ?? '';

                        $matches = [];
                        preg_match_all('/(?<=src=")([^"]+)/', $content, $matches);
                        $imageLinks = $matches[0];

                        foreach ($imageLinks as $image) {
                            if ($image && Str::contains($image, 'freshdesk.com')) {
                                $info = pathinfo(strtok($image, '?'));
                                $filename = Str::random(40);
                                if (isset($info['extension'])) {
                                    $filename .= '.'.$info['extension'];
                                }
                                $name = $imageDisk->putFileAs('support', $image, $filename);

                                if ($name) {
                                    $content = str_replace($image, $imageDisk->url($name), $content);
                                }
                            }
                        }

                        $friendlyUrl = Str::slug($article['title']);
                        $insert = [
                            'title' => $article['title'],
                            'friendly_url' => $friendlyUrl,
                            'folder_id' => $folderId,
                            'content' => $content,
                            'status' => ArticleStatus::PUBLISHED->value,
                            'order' => $articleOrder++,
                            'live_at' => now(),
                            ...$timestamps,
                        ];

                        /** @var \App\Models\FreshDeskArticleStat|null $stats */
                        $stats = FreshDeskArticleStat::query()->where('article_id', $article['id'])->first();
                        if ($stats) {
                            $insert['views'] = $stats->views;
                            $insert['thumbs_up'] = $stats->thumbs_up;
                            $insert['thumbs_down'] = $stats->thumbs_down;
                        }

                        $articleId = DB::table('support_articles')
                            ->insertGetId($insert);

                        $articleMap[$article['id']] = $friendlyUrl;

                        foreach ($fullArticle['tags'] as $topic) {
                            if (! DB::table('support_topics')->where('name', $topic)->exists()) {
                                $topicId = DB::table('support_topics')
                                    ->insertGetId([
                                        'name' => $topic,
                                        ...$timestamps,
                                    ]);
                            } else {
                                $topicId = DB::table('support_topics')
                                    ->where('name', $topic)
                                    ->value('id');
                            }

                            DB::table('support_article_topic')
                                ->insert([
                                    'article_id' => $articleId,
                                    'topic_id' => $topicId,
                                ]);
                        }
                    }
                }
            }

            SupportArticle::query()
                ->eachById(function (SupportArticle $article) use ($articleMap) {
                    $article->timestamps = false;
                    $content = $article->content;

                    $matches = [];
                    preg_match_all('/(?<=href=)("[^"]+")/', $content, $matches);
                    $externalLinks = $matches[0];

                    foreach ($externalLinks as $link) {
                        if (Str::startsWith($link, '"https://app.hylark.com')) {
                            $content = str_replace($link, str_replace('https://app.hylark.com', config('app.url'), $link), $content);
                        } elseif (Str::startsWith($link, '"https://hylark.freshdesk.com')) {
                            // Extract ID from link
                            $matches = [];
                            preg_match('#https://hylark\.freshdesk\.com/(\w{2})?/support/solutions/articles/(\d+)#', $link, $matches);
                            $id = $matches[2] ?? null;
                            if ($id && isset($articleMap[(int) $id])) {
                                $content = str_replace($link, '"'.SupportArticle::generateUrl($articleMap[(int) $id]).'" class="article-link"', $content);
                            }
                        }
                    }

                    $article->forceFill([
                        'content' => $content,
                    ])->save();
                });
        });

        return 0;
    }
}
