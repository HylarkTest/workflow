<?php

declare(strict_types=1);

namespace LaravelUtils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaravelUtils
{
    public static function disableTimestampsForSoftDelete(): void
    {
        Model::getEventDispatcher()->listen('eloquent.deleting: *', function (string $event, array $data) {
            $model = $data[0];

            if (\in_array(SoftDeletes::class, trait_uses_recursive($model), true)) {
                $model->timestamps = false;
            }
        });
        Model::getEventDispatcher()->listen('eloquent.trashed: *', function (string $event, array $data) {
            $model = $data[0];

            $model->timestamps = true;
        });
    }
}
