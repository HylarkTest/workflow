<?php

declare(strict_types=1);

namespace Markers\Models;

use Markers\Models\Concerns\HasMarkers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MarkablePivot extends Pivot implements MarkableModel
{
    use HasMarkers;

    public $incrementing = true;

    /**
     * @var int[]
     */
    protected array $markersToSave = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(static function (self $pivot) {
            $markers = $pivot->getAttribute('markers');
            if ($markers instanceof Marker) {
                $pivot->markersToSave[] = $markers->getKey();
            } elseif ($markers instanceof Collection) {
                $pivot->markersToSave = array_merge($pivot->markersToSave, $markers->modelKeys());
            } elseif (\is_array($markers)) {
                foreach ($markers as $marker) {
                    $pivot->markersToSave[] = $marker instanceof Marker ? $marker->getKey() : $marker;
                }
            }
            unset($pivot->attributes['markers']);
        });

        static::saved(static function (self $pivot) {
            if ($pivot->markersToSave) {
                $pivot->markers()->syncWithoutDetaching($pivot->markersToSave);
            }
        });
    }
}
