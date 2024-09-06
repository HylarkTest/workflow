<?php

declare(strict_types=1);

namespace Finder;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelObserver
{
    /**
     * Indicates if Finder will dispatch the observer's events after all database transactions have committed.
     */
    public bool $afterCommit;

    protected bool $usingSoftDeletes;

    protected bool $forceSaving = false;

    /**
     * The class names that syncing is disabled for.
     *
     * @var array<class-string<\Finder\GloballySearchable>, true>
     */
    protected static array $syncingDisabledFor = [];

    public function __construct()
    {
        $this->afterCommit = Config::get('finder.after_commit', false);
        $this->usingSoftDeletes = Config::get('finder.soft_delete', false);
    }

    /**
     * Enable syncing for the given class.
     *
     * @param  class-string<\Finder\GloballySearchable>  $class
     */
    public static function enableSyncingFor(string $class): void
    {
        unset(static::$syncingDisabledFor[$class]);
    }

    /**
     * Disable syncing for the given class.
     *
     * @param  class-string<\Finder\GloballySearchable>  $class
     */
    public static function disableSyncingFor(string $class): void
    {
        static::$syncingDisabledFor[$class] = true;
    }

    /**
     * Determine if syncing is disabled for the given class or model.
     *
     * @param  class-string<\Finder\GloballySearchable>|\Finder\GloballySearchable  $class
     */
    public static function syncingDisabledFor(string|GloballySearchable $class): bool
    {
        $class = \is_object($class) ? $class::class : $class;

        return isset(static::$syncingDisabledFor[$class]);
    }

    /**
     * Handle the saved event for the model.
     */
    public function saved(GloballySearchable $model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        if (! $this->forceSaving && ! $model->globalSearchIndexShouldBeUpdated()) {
            return;
        }

        if (! $model->shouldBeGloballySearchable()) {
            if ($model->wasGloballySearchableBeforeUpdate()) {
                $model->globallyUnsearchable();
            }

            return;
        }

        $model->globallySearchable();
    }

    /**
     * Handle the deleted event for the model.
     */
    public function deleted(GloballySearchable $model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        if (! $model->wasGloballySearchableBeforeDelete()) {
            return;
        }

        if ($this->usingSoftDeletes && $this->usesSoftDelete($model)) {
            $this->whileForcingUpdate(function () use ($model) {
                $this->saved($model);
            });
        } else {
            $model->globallyUnsearchable();
        }
    }

    /**
     * Handle the force deleted event for the model.
     */
    public function forceDeleted(GloballySearchable $model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        $model->globallyUnsearchable();
    }

    /**
     * Handle the restored event for the model.
     */
    public function restored(GloballySearchable $model): void
    {
        $this->whileForcingUpdate(function () use ($model) {
            $this->saved($model);
        });
    }

    /**
     * Execute the given callback while forcing updates.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    protected function whileForcingUpdate(\Closure $callback): mixed
    {
        $this->forceSaving = true;

        $result = $callback();

        $this->forceSaving = false;

        return $result;
    }

    /**
     * Determine if the given model uses soft deletes.
     */
    protected function usesSoftDelete(GloballySearchable $model): bool
    {
        return \in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }
}
