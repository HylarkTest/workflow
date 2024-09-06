<?php

declare(strict_types=1);

namespace App\Core\GoogleSearchApi;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\RequestException;

class Image
{
    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     *
     * @throws RequestException
     * @throws \JsonException
     */
    public static function search(string $query, int $num = 10, int $start = 1): Collection
    {
        $results = resolve(CustomSearchGateway::class)->search($query, $num, $start);

        return collect((new Result($results))->getItems());
    }
}
