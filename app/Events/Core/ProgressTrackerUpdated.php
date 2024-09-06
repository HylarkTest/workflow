<?php

declare(strict_types=1);

namespace App\Events\Core;

use App\Core\TaskStatus;
use Illuminate\Support\Carbon;

/**
 * @phpstan-type ProgressTrackerData = array{
 *     id: string,
 *     progress: float,
 *     message?: ?string,
 *     processedCount: int|null,
 *     totalCount: int|null,
 *     status: \App\Core\TaskStatus,
 *     estimatedTimeRemaining: int|null,
 *     startedAt: \Illuminate\Support\Carbon,
 *     finishedAt: \Illuminate\Support\Carbon|null,
 * }
 */
class ProgressTrackerUpdated
{
    public readonly string $taskId;

    public readonly ?float $progress;

    public readonly TaskStatus $status;

    public readonly ?int $estimatedTimeRemaining;

    public readonly string $message;

    public readonly ?Carbon $startedAt;

    public readonly ?Carbon $finishedAt;

    public readonly ?int $processedCount;

    public readonly ?int $totalCount;

    public function __construct(array $progressData)
    {
        $this->taskId = $progressData['id'];
        $this->progress = $progressData['progress'];
        $this->status = $progressData['status'];
        $this->estimatedTimeRemaining = $progressData['estimatedTimeRemaining'];
        $this->message = $progressData['message'] ?? '';
        $this->startedAt = $progressData['startedAt'];
        $this->finishedAt = $progressData['finishedAt'];
        $this->processedCount = $progressData['processedCount'];
        $this->totalCount = $progressData['totalCount'];
    }
}
