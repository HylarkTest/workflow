<?php

declare(strict_types=1);

namespace Mappings\Core\Categories;

use GraphQL\Deferred;
use Illuminate\Support\Arr;
use Mappings\Models\CategoryItem;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;

class CategoryItemBatchLoader
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
     * Empty constructor to allow creating static instance
     */
    final public function __construct() {}

    /**
     * Schedule a result to be loaded.
     */
    public function load(string $key, array $metaInfo = []): Deferred
    {
        $this->keys[$key] = $metaInfo;

        return new Deferred(function () use ($key) {
            if (! $this->hasLoaded) {
                $this->results = $this->resolve();
                $this->hasLoaded = true;
            }

            return $this->results[$key] ?? null;
        });
    }

    public function resolve(): array
    {
        return CategoryItem::query()
            ->findMany(Arr::pluck($this->keys, 'key'))
            ->mapWithKeys(
                function (CategoryItem $model): array {
                    $key = $model->getKey();

                    $category = $this->keys[$key]['category'] ?? null;
                    $model = ! $category || $model->category_id === $category ? $model : null;

                    return [$key => $model];
                }
            )
            ->all();
    }

    /**
     * @throws \Exception
     */
    public static function instanceForItem(string $itemId, ?int $categoryId = null): Deferred
    {
        /** @var static $instance */
        $instance = BatchLoaderRegistry::instance([], fn () => new static);

        return $instance->load($itemId, ['key' => $itemId, 'category' => $categoryId]);
    }
}
