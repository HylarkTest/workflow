<?php

declare(strict_types=1);

namespace Mappings\Events;

use Mappings\Models\Item;
use Mappings\Core\Mappings\Relationships\Relationship;

class RelationshipSet
{
    public function __construct(
        public Relationship $relationship,
        public Item $child,
        public Item $parent,
    ) {}
}
