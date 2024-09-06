<?php

declare(strict_types=1);

namespace App\GraphQL;

use GraphQL\Deferred;
use App\Models\Emailable;
use Illuminate\Support\Arr;
use App\Models\ExternalTodoable;
use App\Models\ExternalEventable;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;

class ExternalAssociationBatchLoader
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

    protected array $columnMap = [
        ExternalEventable::class => ['event_id', 'eventable'],
        ExternalTodoable::class => ['todo_id', 'todoable'],
        Emailable::class => ['email_id', 'emailable'],
    ];

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $pivotClass
     */
    final public function __construct(
        protected string $className,
        protected string $pivotClass,
        protected int $sourceId,
    ) {}

    public function resolve(): array
    {
        [$column, $relationName] = $this->columnMap[$this->pivotClass];
        $keys = Arr::collapse(Arr::pluck($this->keys, 'key'));
        $relation = (new $this->pivotClass)->{$relationName}();

        return $this->pivotClass::query()
            ->where('integration_account_id', $this->sourceId)
            ->where("{$relationName}_type", (new $this->className)->getMorphClass())
            ->whereIn($column, $keys)
            ->with($relationName)
            ->get()
            ->groupBy->{$column}
            ->map(
                function (Collection $externalables) use ($relation, $relationName): Collection {
                    return $relation->getRelated()->newCollection(
                        $externalables->map->{$relationName}->filter()->all()
                    );
                }
            )
            ->all();
    }

    /**
     * Schedule a result to be loaded.
     */
    public function loadAndResolve(string|array $key, array $metaInfo = [], ?\Closure $cb = null): Deferred
    {
        $keys = Arr::wrap($key);
        $metaInfo['key'] = $keys;
        $ref = implode('.', $keys);
        $this->keys[$ref] = $metaInfo;

        return new Deferred(function () use ($keys, $cb) {
            if (! $this->hasLoaded) {
                $this->results = $this->resolve();
                $this->hasLoaded = true;
            }

            $model = null;
            foreach ($keys as $key) {
                if (isset($this->results[$key])) {
                    if ($model) {
                        $model = $model->merge($this->results[$key]);
                    } else {
                        $model = $this->results[$key];
                    }
                }
            }

            $model = $model ?? [];

            return $cb ? $cb($model) : $model;
        });
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $pivotClass
     *
     * @throws \Exception
     */
    public static function instanceFromExternal(string $className, string $pivotClass, int $sourceId): self
    {
        /** @var static $instance */
        $instance = BatchLoaderRegistry::instance([
            'lighthouse-helpers',
            $className,
            $pivotClass,
            base64_encode((string) $sourceId), // This needs to be not numeric as numbers are ignored when checking if a new instance is needed
        ], fn () => new static(
            $className,
            $pivotClass,
            $sourceId,
        ));

        return $instance;
    }
}
