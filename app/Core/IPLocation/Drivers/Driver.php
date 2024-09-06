<?php

declare(strict_types=1);

namespace App\Core\IPLocation\Drivers;

use Illuminate\Support\Fluent;
use App\Core\IPLocation\Position;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;

abstract class Driver
{
    /**
     * The fallback driver.
     */
    protected ?Driver $fallback;

    public function __construct(protected Config $config, protected Cache $cache) {}

    /**
     * Append a fallback driver to the end of the chain.
     */
    public function fallback(self $handler): void
    {
        if (! isset($this->fallback)) {
            $this->fallback = $handler;
        } else {
            $this->fallback->fallback($handler);
        }
    }

    /**
     * Handle the driver request.
     *
     * @return false|\App\Core\IPLocation\Position
     *
     * @throws \Exception
     */
    public function get(string $ip): bool|Position
    {
        /** @var \Closure(): (\App\Core\IPLocation\Position|false) $fetchPositionInfo */
        $fetchPositionInfo = function () use ($ip) {
            $data = $this->process($ip);

            $position = $this->getNewPosition();

            // Here we will ensure the locations' data we received isn't empty.
            // Some IP location providers will return empty JSON. We want
            // to avoid this, so we can go to a fallback driver.
            if ($data instanceof Fluent && $this->fluentDataIsNotEmpty($data)) {
                $position = $this->hydrate($position, $data);

                $position->ip = $ip;
                $position->driver = static::class;
            }

            if (! $position->isEmpty()) {
                return $position;
            }

            return isset($this->fallback) ? $this->fallback->get($ip) : false;
        };

        if ($this->config->get('location.cache.enabled')) {
            /** @phpstan-ignore-next-line It doesn't get that the callback returns false */
            return $this->cache->remember(
                $this->config->get('location.cache.key').':'.md5($ip),
                $this->config->get('location.cache.ttl'),
                $fetchPositionInfo
            );
        }

        return $fetchPositionInfo();
    }

    /**
     * Create a new position instance.
     */
    protected function getNewPosition(): Position
    {
        /** @var class-string<\App\Core\IPLocation\Position> $position */
        $position = $this->config->get('location.position', Position::class);

        return new $position;
    }

    /**
     * Determine if the given fluent data is not empty.
     *
     * @param  \Illuminate\Support\Fluent<string, mixed>  $data
     */
    protected function fluentDataIsNotEmpty(Fluent $data): bool
    {
        return ! empty(array_filter($data->getAttributes()));
    }

    /**
     * Get content from the given URL using cURL.
     */
    protected function getUrlContent(string $url): string
    {
        return Http::get($url)->body();
    }

    /**
     * Get the URL to use for querying the current driver.
     */
    abstract protected function url(string $ip): string;

    /**
     * Hydrate the Position object with the given location data.
     *
     * @param  \Illuminate\Support\Fluent<string, mixed>  $location
     */
    abstract protected function hydrate(Position $position, Fluent $location): Position;

    /**
     * Attempt to fetch and process the location data from the driver.
     *
     * @return \Illuminate\Support\Fluent<string, mixed>|bool
     */
    abstract protected function process(string $ip): Fluent|bool;
}
