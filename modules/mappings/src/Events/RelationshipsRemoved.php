<?php

declare(strict_types=1);

namespace Mappings\Events;

use Mappings\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Mappings\Core\Mappings\Relationships\Relationship;

class RelationshipsRemoved
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>  $children
     */
    public function __construct(
        public Relationship $relationship,
        public Collection $children,
        public Item $parent,
    ) {}
}
