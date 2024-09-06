<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Interface Owner
 *
 * @property \Illuminate\Database\Eloquent\Collection<\Mappings\Models\Mapping> $mappings
 *
 * @mixin Model
 */
interface MappingContainer
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation<\Mappings\Models\Mapping>
     */
    public function mappings(): Relation;
}
