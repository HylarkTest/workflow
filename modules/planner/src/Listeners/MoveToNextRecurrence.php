<?php

declare(strict_types=1);

namespace Planner\Listeners;

use Planner\Events\TodoCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class MoveToNextRecurrence implements ShouldQueue
{
    public function handle(TodoCompleted $event): void
    {
        $event->todo->moveToNextRecurrence();
    }
}
