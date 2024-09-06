<?php

declare(strict_types=1);

namespace App\Models;

use LighthouseHelpers\Concerns\HasGlobalId;
use App\Models\Concerns\HasBaseScopedRelationships;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use HasBaseScopedRelationships;
    use HasGlobalId;
}
