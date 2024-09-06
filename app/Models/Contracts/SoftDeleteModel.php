<?php

declare(strict_types=1);

namespace App\Models\Contracts;

/**
 * Interface SoftDeleteModel
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface SoftDeleteModel
{
    /**
     * Force a hard delete on a soft deleted model.
     *
     * @return bool|null
     */
    public function forceDelete();

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function restore();

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function trashed();

    /**
     * Register a restoring model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function restoring($callback);

    /**
     * Register a restored model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function restored($callback);

    /**
     * Determine if the model is currently force deleting.
     *
     * @return bool
     */
    public function isForceDeleting();

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getDeletedAtColumn();

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedDeletedAtColumn();
}
