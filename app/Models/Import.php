<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\TaskStatus;
use Illuminate\Http\UploadedFile;
use App\Jobs\RevertImportedDataJob;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use App\Core\Imports\ImportItemStatus;
use App\Models\Contracts\ProgressTask;
use App\Core\Imports\Importers\RowCounter;
use App\Core\Imports\ImportFileRepository;
use App\Events\Core\ProgressTrackerUpdated;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;

/**
 * Attributes
 *
 * @property int $id
 * @property int $member_id
 * @property string $name
 * @property string $filename
 * @property \App\Core\TaskStatus $status
 * @property string $file_id
 * @property int $processed_rows
 * @property ?int $total_rows
 * @property ?\Illuminate\Support\Carbon $started_at
 * @property ?\Illuminate\Support\Carbon $finished_at
 * @property ?\Illuminate\Support\Carbon $cancelled_at
 * @property ?\Illuminate\Support\Carbon $reverted_at
 * @property ?\Illuminate\Support\Carbon $revert_finished_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \App\Models\BaseUserPivot $member
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\ImportMap> $importables
 */
class Import extends Model implements ProgressTask
{
    use ConvertsCamelCaseAttributes;

    protected $casts = [
        'status' => TaskStatus::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reverted_at' => 'datetime',
        'revert_finished_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\BaseUserPivot, \App\Models\Import>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(BaseUserPivot::class, 'member_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ImportMap>
     */
    public function importables(): HasMany
    {
        return $this->hasMany(ImportMap::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ImportMap>
     */
    public function successfulImportables(): HasMany
    {
        return $this->importables()->whereIn('status', [ImportItemStatus::IMPORTED, ImportItemStatus::REVERTED]);
    }

    public function taskId(): string
    {
        return $this->global_id;
    }

    public static function startImport(string $name, BaseUserPivot $member, UploadedFile|string $file, bool $firstRowIsHeader): self
    {
        $id = md5_file($file instanceof UploadedFile ? $file->path() : $file);
        $extension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($file, PATHINFO_EXTENSION);
        $id .= '.'.$extension;
        $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
        $import = (new self)->forceFill([
            'name' => $name,
            'filename' => $filename,
            'file_id' => $id,
            'status' => TaskStatus::STARTED,
            'started_at' => now(),
        ]);
        $import->member()->associate($member);
        $import->save();
        $import->queueTotalRowCalculation($firstRowIsHeader);
        $import->broadcastTaskProgress();

        return $import;
    }

    public function finishImport(): void
    {
        if ($this->isImporting()) {
            $this->forceFill([
                'status' => TaskStatus::COMPLETED,
                'finished_at' => now(),
            ])->save();

            $this->broadcastTaskProgress();
        }
    }

    public function finishRevert(): void
    {
        $this->forceFill([
            'status' => $this->status === TaskStatus::CANCELLING ? TaskStatus::CANCELLED : TaskStatus::REVERTED,
            'revert_finished_at' => now(),
        ])->save();

        $this->broadcastTaskProgress();
    }

    public function revertImport(): void
    {
        if ($this->status !== TaskStatus::COMPLETED && $this->status !== TaskStatus::FAILED) {
            throw new \RuntimeException('Cannot revert an import that has not finished.');
        }
        $this->forceFill([
            'status' => TaskStatus::REVERTING,
            'reverted_at' => now(),
        ])->save();

        $this->revertImportedData();

        $this->broadcastTaskProgress();
    }

    protected function revertImportedData(): void
    {
        $totalImportables = $this->successfulImportables()->count();
        $chunk = config('hylark.imports.revert_chunk_size', 100);
        $jobs = [];
        for ($i = 0; $i < $totalImportables; $i += $chunk) {
            $jobs[] = new RevertImportedDataJob($this, $chunk, $i);
        }

        Bus::batch($jobs)
            ->name('Reverting import '.$this->id)
            ->progress(function () {
                $this->refresh()->broadcastTaskProgress();
            })
            ->then(function () {
                $this->finishRevert();
            })
            ->catch(function () {
                $this->markAsFailed();
            })
            ->onQueue(config('hylark.imports.revert_queue'))
            ->dispatch();
    }

    public function cancelImport(): void
    {
        if ($this->status !== TaskStatus::STARTED) {
            throw new \RuntimeException('Cannot cancel an import that is not currently running.');
        }
        $this->forceFill([
            'status' => TaskStatus::CANCELLING,
            'cancelled_at' => now(),
            'reverted_at' => now(),
        ])->save();

        $this->revertImportedData();

        $this->broadcastTaskProgress();
    }

    public function markAsFailed(): void
    {
        $this->forceFill([
            'status' => TaskStatus::FAILED,
        ])->save();

        $this->broadcastTaskProgress();
    }

    protected function getImportRepository(): ImportFileRepository
    {
        return resolve(ImportFileRepository::class);
    }

    public function calculateTotalRows(bool $firstRowIsHeader): int
    {
        $repository = $this->getImportRepository();
        $file = $repository->getFilePath($this->file_id);
        $counter = new RowCounter($firstRowIsHeader);
        Excel::import($counter, $file, $repository->getImportsDisk());
        Excel::clearResolvedInstances();

        return $counter->getCount();
    }

    public function queueTotalRowCalculation(bool $firstRowIsHeader): void
    {
        dispatch(function () use ($firstRowIsHeader) {
            $this->forceFill(['total_rows' => $this->calculateTotalRows($firstRowIsHeader)])->save();
            $this->broadcastTaskProgress();
        });
    }

    public function getProgress(): float
    {
        return match ($this->status) {
            TaskStatus::COMPLETED, TaskStatus::REVERTED, TaskStatus::CANCELLED => 1.0,
            TaskStatus::STARTED => $this->total_rows ? $this->processed_rows / $this->total_rows : 0,
            TaskStatus::REVERTING, TaskStatus::CANCELLING => $this->getRevertProgress(),
            default => 0,
        };
    }

    protected function getRevertProgress(): float
    {
        $totalToRevert = $this->successfulImportables()->count();

        return $totalToRevert
            ? $this->importables()->where('status', ImportItemStatus::REVERTED)->count() / $totalToRevert
            : 0.0;
    }

    public function getEstimatedTimeRemaining(): ?int
    {
        if ($this->isEnded()) {
            return 0;
        }
        $progress = $this->getProgress();
        [$startTime, $endTime] = match ($this->status) {
            TaskStatus::STARTED => [$this->started_at, $this->finished_at],
            TaskStatus::REVERTING, TaskStatus::CANCELLING => [$this->reverted_at, $this->revert_finished_at],
            default => [null, null],
        };
        if (! $endTime && $startTime && $progress > 0) {
            $now = microtime(true);
            $elapsedTime = $now - $startTime->getTimestamp();
            $estimatedTotalTime = $elapsedTime / $progress;
            $estimatedTimeRemaining = $estimatedTotalTime - $elapsedTime;

            return (int) $estimatedTimeRemaining;
        }

        return null;
    }

    public function taskProgress(): array
    {
        return [
            'id' => $this->global_id,
            'progress' => $this->getProgress(),
            'processedCount' => $this->processed_rows,
            'totalCount' => $this->total_rows,
            'status' => $this->status,
            'estimatedTimeRemaining' => $this->getEstimatedTimeRemaining(),
            'startedAt' => $this->started_at,
            'finishedAt' => $this->finished_at,
            'cancelledAt' => $this->cancelled_at,
            'revertedAt' => $this->reverted_at,
            'revertFinishedAt' => $this->revert_finished_at,
        ];
    }

    public function broadcastTaskProgress(): void
    {
        event(new ProgressTrackerUpdated($this->taskProgress()));
    }

    public function addProcessedItems(int $count): void
    {
        $this->refresh();
        $this->increment('processed_rows', $count);
        $this->broadcastTaskProgress();
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::COMPLETED;
    }

    public function isImporting(): bool
    {
        return $this->status === TaskStatus::STARTED;
    }

    public function isInProgress(): bool
    {
        return $this->status === TaskStatus::STARTED || $this->status === TaskStatus::REVERTING || $this->status === TaskStatus::CANCELLING;
    }

    public function isEnded(): bool
    {
        return $this->status === TaskStatus::COMPLETED || $this->status === TaskStatus::FAILED || $this->status === TaskStatus::REVERTED || $this->status === TaskStatus::CANCELLED;
    }
}
