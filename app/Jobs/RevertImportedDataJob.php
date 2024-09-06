<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Import;
use App\Models\ImportMap;
use Illuminate\Bus\Batchable;
use Laravel\Scout\Searchable;
use Finder\GloballySearchable;
use App\Core\Imports\ImportItemStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class RevertImportedDataJob implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use SerializesModels;

    public string $queue = 'slow';

    public function __construct(
        protected Import $import,
        protected int $chunk,
        protected int $offset
    ) {}

    public function handle(): void
    {
        $importables = $this->import->successfulImportables()
            ->offset($this->offset)
            ->take($this->chunk)
            ->get();
        $groupedImportables = $importables->groupBy('importable_type');
        $groupedImportables->each(function (Collection $collection, string $importableType) {
            if (! $importableType) {
                return;
            }
            /** @var class-string<\App\Models\Model> $importableModel */
            $importableModel = Relation::getMorphedModel($importableType);
            $isSearchable = in_array(Searchable::class, class_uses_recursive($importableModel));
            $isGloballySearchable = is_a($importableModel, GloballySearchable::class, true);

            if ($isSearchable) {
                /** @phpstan-ignore-next-line We know this exists */
                $importableModel::disableSearchSyncing();
            }
            if ($isGloballySearchable) {
                $importableModel::disableGlobalSearchSyncing();
            }
            $models = $importableModel::whereIn('id', $collection->pluck('importable_id'))->get();
            $models->each(function (Model $importable) {
                if (method_exists($importable, 'forceDelete')) {
                    $importable->forceDelete();
                } else {
                    $importable->delete();
                }
            });

            $originalScoutQueue = config('scout.queue');
            $originalFinderQueue = config('finder.queue');
            config([
                'scout.queue' => false,
                'finder.queue' => false,
            ]);
            if ($isSearchable) {
                $models->unsearchable();
                /** @phpstan-ignore-next-line We know this exists */
                $importableModel::enableSearchSyncing();
            }
            if ($isGloballySearchable) {
                $models->globallyUnsearchable();
                $importableModel::enableGlobalSearchSyncing();
            }
            config([
                'scout.queue' => $originalScoutQueue,
                'finder.queue' => $originalFinderQueue,
            ]);
            ImportMap::whereIn('importable_id', $models->modelKeys())
                ->where('importable_type', $importableType)
                ->update([
                    'status' => ImportItemStatus::REVERTED,
                    'importable_id' => null,
                ]);
        });
    }
}
