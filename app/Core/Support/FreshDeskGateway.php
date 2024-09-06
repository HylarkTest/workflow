<?php

declare(strict_types=1);

namespace App\Core\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

/**
 * Class SupportRepository
 *
 * @phpstan-type SupportArticle array{
 *     id: int,
 *     title: string,
 *     description?: string,
 *     description_text?: string,
 *     tags: string[],
 *     hits: int,
 *     created_at: string,
 * }
 * @phpstan-type SupportFolder array{
 *     id?: int,
 *     name: string,
 *     description?: string,
 *     articles?: array<int, SupportArticle>,
 *     articles_count?: int
 * }
 * @phpstan-type SupportCategory array{
 *     id: int,
 *     name: string,
 *     description?: string,
 *     folders?: array<int, SupportFolder>
 * }
 */
class FreshDeskGateway
{
    protected PendingRequest $client;

    protected string $url;

    public function __construct()
    {
        $this->client = Http::withBasicAuth(config('services.freshdesk.api_key'), 'X');
        $this->url = config('services.freshdesk.url');
    }

    /**
     * @return SupportCategory[]
     */
    public function getCategories(): array
    {
        return $this->client->get($this->url.'/solutions/categories')->json();
    }

    /**
     * @return SupportFolder[]
     */
    public function getFolders(int $categoryId): array
    {
        return $this->client->get($this->url.'/solutions/categories/'.$categoryId.'/folders')->json();
    }

    /**
     * @return SupportFolder[]
     */
    public function getSubFolders(int $folderId): array
    {
        return $this->client->get($this->url.'/solutions/folders/'.$folderId.'/subfolders')->json();
    }

    /**
     * @return SupportArticle[]
     */
    public function getArticles(int $folderId): array
    {
        return $this->client->get($this->url.'/solutions/folders/'.$folderId.'/articles')->json();
    }

    /**
     * @return SupportArticle
     */
    public function getArticle(int $id): array
    {
        $response = $this->client->get($this->url.'/solutions/articles/'.$id);
        if ($response->status() === 404) {
            abort(404);
        }
        if ($response->status() !== 200) {
            abort(500);
        }

        return $response->json();
    }
}
