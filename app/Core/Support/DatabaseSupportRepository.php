<?php

declare(strict_types=1);

namespace App\Core\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Models\Support\SupportTopic;
use App\Models\Support\SupportFolder;
use App\Models\Support\SupportArticle;
use App\Models\Support\SupportCategory;
use Elastic\ScoutDriverPlus\Support\Query;
use Finder\Builders\MatchBoolPrefixQueryBuilder;
use Finder\Builders\DisMaxMatchPrefixQueryBuilder;

/**
 * @phpstan-import-type SupportArticle from SupportRepositoryInterface as SupportArticleInfo
 */
class DatabaseSupportRepository implements SupportRepositoryInterface
{
    /**
     * @return array[]
     */
    public function getSupportCategories(): array
    {
        return $this->mapCategories($this->fetchCategories());
    }

    public function getPopularCategories(): array
    {
        return $this->fetchCategories()
            ->each(fn (SupportCategory $category) => $category->setRelation('articles', $category->folders->flatMap->articles))
            ->sortByDesc(fn ($category) => $category->articles->avg('hits'))
            ->map(function (SupportCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'articles' => $this->mapArticles(
                        $category->articles
                            ->sortByDesc('views')
                            ->values()
                            ->take(3)
                    ),
                ];
            })
            ->values()
            ->where('articles')
            ->all();
    }

    /**
     * @return array[]
     */
    public function getSupportArticles(): array
    {
        return $this->mapArticles(SupportArticle::getCachedArticles());
    }

    /**
     * @return array[]
     */
    public function getMostRecentArticles(?array $topics = []): array
    {
        return $this->mapArticles(
            SupportArticle::getCachedMostRecentArticles($topics)
        );
    }

    /**
     * @return array[]
     */
    public function getRecommendedArticles(?array $topics = []): array
    {
        return $this->mapArticles(
            SupportArticle::getCachedRecommendedArticles($topics)
        );
    }

    /**
     * @return array[]
     */
    public function searchArticles(string $query, ?array $topics = []): array
    {
        $topics = SupportTopic::sanitizeTopicIds($topics);
        /** @var \Illuminate\Support\Collection<int, \App\Models\Support\SupportArticle> $articles */
        $articles = SupportArticle::searchQuery(
            Query::bool()
                ->should((new DisMaxMatchPrefixQueryBuilder)->field('title')->query($query)->fuzziness('AUTO')->boost(3))
                ->should((new MatchBoolPrefixQueryBuilder)->field('topics.name')->query($query)->boost(2))
                ->should((new DisMaxMatchPrefixQueryBuilder)->field('content')->query($query)->fuzziness('AUTO')->boost(1))
                ->minimumShouldMatch(1)
                ->when($topics, fn ($query, array $topics) => $query->filter(
                    Query::nested()->path('topics')->query(
                        Query::terms()->field('topics.id')->values($topics)
                    )
                ))
        )->execute()->models()->load('topics')->toBase();

        return $this->mapArticles($articles);
    }

    public function getTopics(): array
    {
        return $this->mapTopics(SupportTopic::getCachedTopics());
    }

    public function getPopularTopics(): array
    {
        return $this->mapTopics(SupportTopic::getCachedPopularTopics());
    }

    /**
     * @return SupportArticleInfo
     */
    public function getArticle(string $id): array
    {
        $article = SupportArticle::getCachedArticle($id);

        $content = $article->content;
        str_replace(config('hylark.production_url'), config('app.url'), $content);

        return [
            'id' => $article->id,
            'title' => $article->title,
            'topics' => $this->mapTopics($article->topics),
            'description' => $content,
            'hits' => $article->views,
            'thumbsUp' => $article->thumbs_up,
            'thumbsDown' => $article->thumbs_down,
            'createdAt' => (string) $article->created_at,
            'updatedAt' => (string) $article->updated_at,
            'liveAt' => (string) $article->live_at,
        ];
    }

    public function getFolder(int $folderId): array
    {
        $folder = SupportFolder::getCachedFolder($folderId);

        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'articlesCount' => $folder->articles->count(),
            'articles' => $this->mapArticles($folder->articles),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Support\SupportCategory>
     */
    protected function fetchCategories(): Collection
    {
        return SupportCategory::getCachedCategories();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Support\SupportCategory>  $categories
     */
    protected function mapCategories(Collection $categories): array
    {
        return $categories->map(function (SupportCategory $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'folders' => $category->folders
                    ->map(fn (SupportFolder $folder) => [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'articlesCount' => $folder->articles->count(),
                        'articles' => $this->mapArticles($folder->articles),
                    ])->all(),
            ];
        })->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Support\SupportArticle>  $articles
     */
    protected function mapArticles(Collection $articles): array
    {
        return $articles->map(function (SupportArticle $article) {
            return [
                'id' => $article->id,
                'friendlyUrl' => $article->friendly_url,
                'title' => $article->title,
                'descriptionTrimmed' => Str::limit($article->stripped_content, 60),
                'topics' => $this->mapTopics($article->topics),
                'hits' => $article->views,
                'thumbsUp' => $article->thumbs_up,
                'thumbsDown' => $article->thumbs_down,
                'createdAt' => (string) $article->created_at,
            ];
        })->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Support\SupportTopic>  $topics
     */
    protected function mapTopics(Collection $topics): array
    {
        return $topics->map(fn (SupportTopic $topic) => [
            'id' => $topic->id,
            'name' => $topic->name,
            'key' => $topic->friendly_id,
        ])->all();
    }
}
