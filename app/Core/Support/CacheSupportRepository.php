<?php

declare(strict_types=1);

namespace App\Core\Support;

use Fuse\Fuse;
use Illuminate\Support\Collection;
use Stancl\Tenancy\Facades\Tenancy;
use App\Models\FreshDeskArticleStat;
use Elastic\ScoutDriverPlus\Support\Arr;

/**
 * Class SupportRepository
 *
 * @phpstan-import-type SupportArticle from FreshDeskGateway
 * @phpstan-import-type SupportCategory from FreshDeskGateway
 * @phpstan-import-type SupportFolder from FreshDeskGateway
 */
class CacheSupportRepository
{
    public function __construct(protected FreshDeskGateway $gateway) {}

    /**
     * @return SupportCategory[]
     */
    public function getSupportCategories(): array
    {
        return $this->getCategories(true);
    }

    public function getPopularCategories(): array
    {
        $categories = $this->getCategories();
        $stats = FreshDeskArticleStat::query()->orderByDesc('views')->get();
        foreach ($categories as &$category) {
            $category['articles'] = $this->getArticlesFromFolder($category);
            $category['average_view_count'] = $stats->filter(
                fn ($stat) => Arr::first($category['articles'], fn ($article) => $article['id'] === $stat->article_id)
            )->avg('views');
        }
        $sortedStats = $stats->pluck('article_id');

        return collect($categories)->sortByDesc('average_view_count')
            ->take(4)
            ->map(function ($category) use ($sortedStats) {
                unset($category['folders'], $category['average_view_count']);
                $category['articles'] = $this->takeOrderedArticles($category['articles'], $sortedStats);

                return $category;
            })->filter(fn ($category) => (bool) \count($category['articles']))->values()->all();
    }

    /**
     * @return SupportArticle[]
     */
    public function getSupportArticles(): array
    {
        $categories = $this->getCategories();
        $articles = [];
        foreach ($categories as $category) {
            $articles = [
                ...$articles,
                ...$this->getArticlesFromFolder($category),
            ];
        }

        return $articles;
    }

    /**
     * @return SupportArticle[]
     */
    public function getMostRecentArticles(): array
    {
        return $this->takeMostRecentArticles($this->getSupportArticles());
    }

    /**
     * @return SupportArticle[]
     */
    public function getRecommendedArticles(): array
    {
        return $this->takeTopRecommendedArticles($this->getSupportArticles());
    }

    /**
     * @return SupportArticle[]
     */
    public function searchArticles(string $query): array
    {
        $articles = $this->getSupportArticles();
        $articlesIndex = new Fuse(
            $articles,
            [
                'keys' => [
                    [
                        'name' => 'title',
                        'weight' => 2,
                    ],
                    [
                        'name' => 'tags',
                        'weight' => 1,
                    ],
                ],
                'threshold' => 0.3,
            ]
        );

        return Arr::pluck($articlesIndex->search($query), 'item');
    }

    /**
     * @return string[]
     */
    public function getTopics(): array
    {
        $categories = $this->getCategories();
        $tags = [];
        foreach ($categories as $category) {
            $tags = [
                ...$tags,
                ...$this->getTagsFromFolder($category),
            ];
        }

        return $tags;
    }

    /**
     * @return string[]
     */
    public function getPopularTopics(): array
    {
        $tags = array_count_values($this->getTopics());

        arsort($tags);

        return \array_slice(array_keys($tags), 0, 5);
    }

    /**
     * @return SupportArticle
     */
    public function getArticle(string $id): array
    {
        $article = $this->gateway->getArticle((int) $id);
        /** @var \App\Models\FreshDeskArticleStat $stats */
        $stats = FreshDeskArticleStat::query()->find($id);
        $article['hits'] = $stats->views ?? 0;
        $article['thumbs_up'] = $stats->thumbs_up ?? 0;
        $article['thumbs_down'] = $stats->thumbs_down ?? 0;

        return $article;
    }

    /**
     * @return SupportFolder
     */
    public function getFolder(int $folderId): array
    {
        $categories = $this->getCategories(true);
        $folders = Arr::collapse(Arr::pluck($categories, 'folders'));

        return Arr::first($folders, fn ($folder) => $folder['id'] === $folderId);
    }

    /**
     * @param  SupportArticle[]  $articles
     * @return SupportArticle[]
     */
    public function takeMostRecentArticles(array $articles): array
    {
        return collect($articles)->sortByDesc('created_at')->take(3)->values()->all();
    }

    protected function getCategories(bool $collapseFolders = false): array
    {
        $categories = Tenancy::central(fn () => cache()->get('freshdesk.categories'));
        if ($collapseFolders) {
            foreach ($categories as &$category) {
                $category['folders'] = $this->collapseFolders($category['folders']);
            }
        }

        return $categories;
    }

    /**
     * @param  SupportArticle[]  $articles
     * @return SupportArticle[]
     */
    protected function takeTopRecommendedArticles(array $articles): array
    {
        $stats = FreshDeskArticleStat::query()
            ->selectRaw('article_id, thumbs_up - thumbs_down as score, views')
            ->orderByDesc('score')
            ->orderByDesc('views')
            ->pluck('article_id');

        return $this->takeOrderedArticles($articles, $stats);
    }

    /**
     * @param  SupportArticle[]  $articles
     * @return SupportArticle[]
     */
    protected function takeMostPopularArticles(array $articles): array
    {
        $stats = FreshDeskArticleStat::query()
            ->orderByDesc('views')
            ->pluck('article_id');

        return $this->takeOrderedArticles($articles, $stats);
    }

    /**
     * @param  SupportArticle[]  $articles
     * @param  \Illuminate\Support\Collection<int, int>  $orderedStats
     */
    protected function takeOrderedArticles(array $articles, Collection $orderedStats): array
    {
        return collect($articles)
            ->sort(function ($article1, $article2) use ($orderedStats) {
                $id1 = $orderedStats->search($article1['id']);
                $id2 = $orderedStats->search($article2['id']);

                if ($id1 === false && $id2 === false) {
                    return $article1['created_at'] <=> $article2['created_at'];
                }
                if ($id1 === false) {
                    return 1;
                }
                if ($id2 === false) {
                    return -1;
                }

                return $id1 <=> $id2;
            })->take(3)->values()->all();
    }

    /**
     * @param  SupportFolder|SupportCategory  $folder
     * @return SupportArticle[]
     */
    protected function getArticlesFromFolder(array $folder): array
    {
        $articles = [];
        if (isset($folder['articles'])) {
            $articles = [
                ...$articles,
                ...$folder['articles'],
            ];
        }
        if (isset($folder['folders'])) {
            foreach ($folder['folders'] as $subFolder) {
                $articles = [
                    ...$articles,
                    ...$this->getArticlesFromFolder($subFolder),
                ];
            }
        }

        return $articles;
    }

    /**
     * @param  SupportFolder[]|SupportCategory[]  $folders
     * @return SupportFolder[]
     */
    protected function collapseFolders(array $folders, string $prefix = ''): array
    {
        $collapsed = [];
        foreach ($folders as $folder) {
            $collapsed[] = [
                ...$folder,
                'name' => $prefix.$folder['name'],
                'articles' => array_map(
                    fn (array $article) => Arr::only($article, ['id', 'title', 'created_at']),
                    $folder['articles'] ?? []
                ),
            ];
            if (isset($folder['folders'])) {
                $collapsed = [
                    ...$collapsed,
                    ...$this->collapseFolders($folder['folders'], $prefix.$folder['name'].'/'),
                ];
            }
        }

        /** @phpstan-ignore-next-line */
        return $collapsed;
    }

    /**
     * @param  SupportFolder|SupportCategory  $folder
     * @return string[]
     */
    protected function getTagsFromFolder(array $folder): array
    {
        $tags = [];
        if (isset($folder['articles'])) {
            $tags = [
                ...$tags,
                ...Arr::collapse(Arr::pluck($folder['articles'], 'tags')),
            ];
        }
        if (isset($folder['folders'])) {
            foreach ($folder['folders'] as $subFolder) {
                $tags = [
                    ...$tags,
                    ...$this->getTagsFromFolder($subFolder),
                ];
            }
        }

        return $tags;
    }
}
