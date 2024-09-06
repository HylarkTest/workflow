<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use GraphQL\Deferred;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use Illuminate\Container\Container;
use PHPStan\ShouldNotHappenException;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;

class ModelBatchLoader
{
    /**
     * Map from keys to metainfo for resolving.
     *
     * @var array[]
     */
    protected array $keys = [];

    /**
     * Map from keys to resolved values.
     */
    protected array $results = [];

    /**
     * Check if data has been loaded.
     */
    protected bool $hasLoaded = false;

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     */
    final public function __construct(protected string $className, protected bool $withTrashed = false) {}

    /**
     * Schedule a result to be loaded.
     */
    public function load(mixed $key, array $metaInfo = []): Deferred
    {
        return $this->loadAndResolve($key, $metaInfo);
    }

    public function resolve(): array
    {
        return $this->className::query()
            ->when($this->withTrashed, function ($query) {
                if (! method_exists($query, 'withTrashed') && ! $query->hasMacro('withTrashed')) {
                    throw new ShouldNotHappenException('The model does not support soft deletes');
                }
                /** @phpstan-ignore-next-line Checked above */
                $query->withTrashed();
            })
            ->findMany(Arr::pluck($this->keys, 'key'))
            ->mapWithKeys(
                function (Model $model): array {
                    $key = $model->getKey();

                    return [$key => $model];
                }
            )
            ->all();
    }

    /**
     * Schedule a result to be loaded.
     */
    public function loadAndResolve(mixed $key, array $metaInfo = [], ?\Closure $cb = null): Deferred
    {
        if ($this->hasLoaded) {
            $this->hasLoaded = false;
        }
        $this->keys[$key] = [
            'key' => $key,
            ...$metaInfo,
        ];

        return new Deferred(function () use ($key, $cb) {
            if (! $this->hasLoaded) {
                $this->results = $this->resolve();
                $this->hasLoaded = true;
            }

            $model = $this->results[$key] ?? null;

            return $cb ? $cb($model) : $model;
        });
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     *
     * @throws \Exception
     */
    public static function instanceFromModel(string $className, bool $withTrashed = false): self
    {
        $path = ['lighthouse-helpers', $className];
        if ($withTrashed) {
            $path[] = 'withTrashed';
        }
        /** @var static $instance */
        $instance = BatchLoaderRegistry::instance($path, fn () => new static($className, $withTrashed));

        return $instance;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function instanceFromGlobalId(string $globalId, ?\Closure $cb = null, ?GlobalId $globalIdService = null): Deferred
    {
        $globalIdService = $globalIdService ?: Container::getInstance()->make(GlobalId::class);
        [$type, $id] = $globalIdService->decode($globalId);
        $className = Utils::namespaceModelClass($type);

        throw_if(! $className, GlobalIdException::class, "Could not find an entity with the global ID $globalId");

        return static::instanceFromModel($className)->loadAndResolve($id, [], $cb);
    }
}
