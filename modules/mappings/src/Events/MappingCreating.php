<?php

declare(strict_types=1);

namespace Mappings\Events;

use Mappings\Models\Mapping;
use Illuminate\Queue\SerializesModels;

class MappingCreating
{
    use SerializesModels;

    public Mapping $mapping;

    /**
     * MappingCreated constructor.
     */
    public function __construct(Mapping $mapping)
    {
        $this->mapping = $mapping;
    }
}
