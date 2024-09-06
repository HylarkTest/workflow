<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use App\Models\FreshDeskArticleStat;
use App\Core\Support\FreshDeskGateway;

class FreshDeskCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freshdesk:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve and cache all FreshDesk solution articles.';

    protected FreshDeskGateway $gateway;

    protected array $keysToCache = [
        'categories' => [
            'id',
            'name',
            'description',
            'folders',
        ],
        'folders' => [
            'id',
            'name',
            'description',
            'sub_folders',
            'articles',
            'articles_count',
        ],
        'articles' => [
            'id',
            'title',
            'description',
            'description_text',
            'attachments',
            'type',
            'tags',
            'created_at',
            'updated_at',
        ],
    ];

    protected array $articleIds = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(FreshDeskGateway $gateway)
    {
        $this->gateway = $gateway;

        $this->info('Retrieving FreshDesk solution articles...');

        /** @var array<int, array> $categories */
        $categories = $this->gateway->getCategories();
        $categories = collect($categories)->filter(
            fn ($category) => $category['name'] !== 'Internal'
                && (! isset($category['visible_in_portals'])
                || \in_array((int) config('services.freshdesk.portal_id'), $category['visible_in_portals'], true))
        );

        $categories = $categories->map(function ($category) {
            $this->info('Retrieving folders and articles for category: '.$category['name']);
            /** @var array<int, array> $folders */
            $folders = $this->gateway->getFolders($category['id']);
            $folders = collect($folders);

            return Arr::only([
                ...$category,
                'folders' => $folders->map(fn ($folder) => $this->populateFolders($folder))->all(),
            ], $this->keysToCache['categories']);
        })->values();

        cache()->forever('freshdesk.categories', $categories->all());

        $this->info('Pruning FreshDesk article stats...');
        $this->pruneArticleStats();

        $this->info('FreshDesk solution articles cached successfully.');

        return Command::SUCCESS;
    }

    protected function populateFolders(array $folder): array
    {
        // Recursively populate folders with subfolders and articles from freshdesk
        if ($folder['sub_folders_count'] ?? 0) {
            /** @var array<int, array> $subFolders */
            $subFolders = $this->gateway->getSubFolders($folder['id']);
            $subFolders = collect($subFolders)->map(fn ($subFolder) => $this->populateFolders($subFolder));
            $folder['folders'] = $subFolders->all();
        }
        if ($folder['articles_count'] ?? 0) {
            /** @var array<int, array> $articles */
            $articles = $this->gateway->getArticles($folder['id']);
            $articles = collect($articles)->filter(fn ($article) => $article['status'] === 2)
                ->map(fn ($article) => Arr::only($article, $this->keysToCache['articles']))
                ->values();
            $folder['articles'] = $articles->all();
            $this->articleIds = array_merge($this->articleIds, $articles->pluck('id')->all());
        }

        return Arr::only($folder, $this->keysToCache['folders']);
    }

    protected function pruneArticleStats(): void
    {
        FreshDeskArticleStat::query()
            ->whereKeyNot($this->articleIds)
            ->delete();
    }
}
