<?php

declare(strict_types=1);

namespace App\Core\GoogleSearchApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class CustomSearchGateway
{
    protected string $url;

    protected string $key;

    protected string $cx;

    public function __construct()
    {
        $this->url = config('services.search_api.url');
        $this->key = config('services.search_api.api_key');
        $this->cx = config('services.search_api.engine_id');
    }

    /**
     * @throws RequestException
     */
    public function search(string $query, int $num = 10, int $start = 1): array
    {
        $body = $this->getRequestBody($query, $num, $start);

        return $this->getResults(requestBody: $body);
    }

    /**
     * @throws RequestException
     */
    public function getResults(array $requestBody): array
    {
        return Http::withHeaders(['referer' => config('app.url')])
            ->get($this->url, $requestBody)
            ->throw()
            ->json();
    }

    protected function getUrlContent(string $url): string
    {
        return Http::get($url)->body();
    }

    protected function getRequestBody(string $query, int $num = 10, int $start = 1): array
    {
        return [
            'key' => $this->key,
            'cx' => $this->cx,
            'q' => $query,
            'searchType' => 'image',
            // 'imgSize' => 'medium', // The images are resized automatically so there is no need to restrict the size of the results
            'safe' => 'active',
            // 'excludeTerms' => 'logo-download large xl xxl wp-content',
            'fileType' => 'bmp, gif, jpeg, png, webp',
            'num' => $num,
            'start' => $start,
        ];
    }
}
