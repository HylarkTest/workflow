<?php

declare(strict_types=1);

namespace Markers\Models\Collections;

use LaravelUtils\Database\Eloquent\Collections\SortableCollection;

/**
 * @extends \LaravelUtils\Database\Eloquent\Collections\SortableCollection<array-key, \Markers\Models\Marker>
 */
class MarkerCollection extends SortableCollection {}
