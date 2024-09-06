<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Import;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CanBeImported
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Import>
     */
    public function imports(): MorphToMany
    {
        return $this->morphToMany(Import::class, 'importable', 'imports_map');
    }
}
