<?php

declare(strict_types=1);

namespace Timekeeper\Models\Collections;

use LaravelUtils\Database\Eloquent\Collections\SortableCollection;

/**
 * @extends \LaravelUtils\Database\Eloquent\Collections\SortableCollection<array-key, \Deadlines\Models\Deadline>
 */
class DeadlineCollection extends SortableCollection {}
