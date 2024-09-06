<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Mapping;
use Illuminate\Queue\SerializesModels;

class MappingSaved
{
    use SerializesModels;

    public function __construct(public Mapping $mapping) {}
}
