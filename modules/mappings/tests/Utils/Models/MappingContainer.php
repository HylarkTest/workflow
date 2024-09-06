<?php

declare(strict_types=1);

namespace Tests\Mappings\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Relations\Relation;

class MappingContainer extends Model implements \Mappings\Core\Mappings\Contracts\MappingContainer
{
    use HasGlobalId;

    public $timestamps = false;

    public function mappings(): Relation
    {
        return $this->hasMany(config('mappings.models.mapping'));
    }

    protected function typeName(): string
    {
        return __CLASS__;
    }
}
