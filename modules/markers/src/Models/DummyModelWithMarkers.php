<?php

declare(strict_types=1);

namespace Markers\Models;

use Illuminate\Database\Eloquent\Model;
use Markers\Models\Concerns\HasAllMarkers;

/**
 * A dummy model that uses the marker traits for PHPStan.
 *
 * Class DummyModelWithMarkers
 */
class DummyModelWithMarkers extends Model
{
    use HasAllMarkers;
}
