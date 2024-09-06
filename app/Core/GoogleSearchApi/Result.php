<?php

declare(strict_types=1);

namespace App\Core\GoogleSearchApi;

class Result
{
    protected array|string $json;

    protected array $results;

    public function __construct(array|string $json)
    {
        $this->json = $json;
    }

    /**
     * @throws \JsonException
     */
    public function getItems(): array
    {
        if (\is_string($this->json)) {
            self::asJson($this->json);
        }

        return $this->json['items'] ?? [];
    }

    /**
     * @throws \JsonException
     */
    public static function asJson(string $json): mixed
    {
        return json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
    }
}
