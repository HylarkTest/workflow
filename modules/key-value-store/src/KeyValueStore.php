<?php

declare(strict_types=1);

namespace KeyValueStore;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\Connection;

class KeyValueStore
{
    protected Connection $redis;

    public function __construct()
    {
        $this->redis = Redis::connection(config('key-value-store.store'));
    }

    /**
     * @return Collection<int, string>
     */
    public function getKeysForScope(string $scope): Collection
    {
        $prefix = $this->getPrefix($scope);
        /** @var string[] $keys */
        $keys = $this->redis->keys("$prefix*");

        $prefix = $this->getPrefix($scope);

        return collect($this->stripRedisPrefix($keys))
            ->map(fn (string $key): string => (string) preg_replace("/^$prefix/", '', $key));
    }

    public function getValue(string $key, string $scope): mixed
    {
        $value = $this->redis->get($this->buildScopedKey($key, $scope));

        return $value ? json_decode($value, true, 512, \JSON_THROW_ON_ERROR) : null;
    }

    public function storeValue(string $key, string $scope, mixed $value, ?int $ttl = null): bool
    {
        $value = json_encode($value, \JSON_THROW_ON_ERROR);
        $key = $this->buildScopedKey($key, $scope);

        if ($maxBytes = config('key-value-store.max-bytes')) {
            // PHP CS Fixer requires `mb_strlen` so to calculate the actual number of bytes we need to use
            // ASCII encoding.
            throw_if(mb_strlen($value, 'ASCII') > $maxBytes, ValueTooBigException::class);
        }

        if ($maxTtl = config('key-value-store.max-ttl')) {
            $ttl = $ttl ? min([$ttl, $maxTtl]) : $maxTtl;
        }

        if ($maxKeys = config('key-value-store.max-keys')) {
            $currentKeysCount = \count($this->redis->keys($this->getPrefix($scope).'*'));
            throw_if($currentKeysCount >= $maxKeys, TooManyKeysException::class);
        }

        if ($ttl) {
            $this->redis->setEx($key, $ttl, $value);
        } else {
            $this->redis->set($key, $value);
        }

        return true;
    }

    public function deleteValue(string $key, string $scope): bool
    {
        $key = $this->buildScopedKey($key, $scope);
        $this->redis->del($key);

        return true;
    }

    public function deleteScope(string $scope): bool
    {
        $prefix = $this->getPrefix($scope);
        $keys = $this->redis->keys("$prefix*");

        /** @phpstan-ignore-next-line  */
        $this->redis->del($this->stripRedisPrefix($keys));

        return true;
    }

    public function deleteAllKeys(string $key): bool
    {
        $prefix = $this->getPrefix('*');
        $keys = $this->redis->keys("$prefix$key");

        /** @phpstan-ignore-next-line  */
        $this->redis->del($this->stripRedisPrefix($keys));

        return true;
    }

    public function deleteAll(): bool
    {
        $prefix = config('key-value-store.key-prefix');
        $keys = $this->redis->keys("$prefix*");

        /** @phpstan-ignore-next-line  */
        $this->redis->del($this->stripRedisPrefix($keys));

        return true;
    }

    protected function buildScopedKey(string $key, string $scope): string
    {
        $prefix = $this->getPrefix($scope);

        return "$prefix$key";
    }

    protected function getPrefix(string $scope): string
    {
        $prefix = config('key-value-store.key-prefix');
        $prefix = $prefix ? "$prefix:" : '';

        return "$prefix$scope:";
    }

    /**
     * @template T of array|string
     *
     * @param  T  $keys
     * @return T
     */
    protected function stripRedisPrefix(string|array $keys): string|array
    {
        if (\is_string($keys)) {
            return $this->stripRedisPrefix([$keys])[0];
        }
        $prefix = config('database.redis.options.prefix', '');

        return $prefix ? array_map(fn (string $key): string => (string) preg_replace("/^$prefix/", '', $key), $keys) : $keys;
    }
}
