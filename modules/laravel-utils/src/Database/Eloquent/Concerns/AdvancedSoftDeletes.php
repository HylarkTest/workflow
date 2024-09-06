<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Jobs\RestoreCascadeJob;
use LaravelUtils\Jobs\SoftDeleteCascadeJob;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property ?string $deleted_by
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AdvancedSoftDeletes
{
    use SoftDeletes;

    public static function bootAdvancedSoftDeletes(): void
    {
        static::deleting(function (self $model) {
            $model->timestamps = false;
            $relationships = $model->getDeleteCascadeRelationships();
            foreach ($relationships as $key => $value) {
                $relationship = \is_int($key) ? $value : $key;
                $options = \is_int($key) ? [] : (array) $value;

                /** @var \Illuminate\Database\Eloquent\Relations\HasOneOrMany $relation */
                $relation = $model->{$relationship}();

                if ($model->isForceDeleting()) {
                    $relation->withoutGlobalScopes()
                        ->orderBy($relation->getRelated()->getKeyName())
                        ->eachById(function (Model $child) {
                            $child->forceDelete();
                        });
                } else {
                    $parentId = $relation instanceof BelongsTo
                        ? $relation->getParentKey()
                        : $model->getKey();

                    $foreignColumn = $relation instanceof BelongsTo
                        ? $relation->getQualifiedOwnerKeyName()
                        : $relation->getQualifiedForeignKeyName();

                    $dispatchMethod = \in_array('queue', $options, true) ? 'dispatch' : 'dispatch_sync';

                    $dispatchMethod(new SoftDeleteCascadeJob(
                        $parentId,
                        \get_class($relation->getRelated()),
                        $foreignColumn,
                        $relation instanceof MorphMany ? $relation->getMorphType() : null,
                    ));
                }
            }
        });

        static::restored(function (self $model) {
            $relationships = $model->getDeleteCascadeRelationships();
            foreach ($relationships as $relationship) {
                RestoreCascadeJob::dispatch($model, $relationship);
            }
        });

        static::restoring(function (self $model) {
            $model->timestamps = false;
            if ($model->deleted_by) {
                $model->deleted_by = null;
            }
        });
    }

    public function deleteBy(string $idColumn, ?string $morphColumn = null): void
    {
        $column = $idColumn;
        if ($morphColumn) {
            $column = "$morphColumn::$column";
        }
        $this->deleted_by = $column;
        $this->delete();
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        if ($this->deleted_by) {
            $columns['deleted_by'] = $this->deleted_by;
        }

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && $this->getUpdatedAtColumn() !== null) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }

    protected function getDeleteCascadeRelationships(): array
    {
        return $this->deleteCascadeRelationships ?? [];
    }
}
