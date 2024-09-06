<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use Notes\Models\Notebook as BaseNotebook;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Notebook
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Note> notes()
 *
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Note, \App\Models\Notebook>
 */
class Notebook extends BaseNotebook implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Note, \App\Models\Notebook> */
    use HasFeatureListMethods;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Note>
     */
    public function children(): HasMany
    {
        return $this->notes();
    }
}
