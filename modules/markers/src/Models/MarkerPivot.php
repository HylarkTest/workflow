<?php

declare(strict_types=1);

namespace Markers\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class MarkerPivot extends MorphPivot
{
    protected $dateFormat = 'Y-m-d H:i:s.u';
}
