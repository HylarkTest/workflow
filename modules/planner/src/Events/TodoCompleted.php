<?php

declare(strict_types=1);

namespace Planner\Events;

use Planner\Models\Todo;
use Illuminate\Queue\SerializesModels;

class TodoCompleted
{
    use SerializesModels;

    public function __construct(public Todo $todo) {}
}
