<?php

declare(strict_types=1);

namespace Markers\Events;

use Markers\Models\Marker;
use Illuminate\Database\Eloquent\Model;

class MarkerRemoved
{
    public function __construct(public Marker $marker, public Model $markable) {}
}
