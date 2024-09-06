<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use App\Core\Actions\PrivateActionSubject;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface SavedFilterModel extends PrivateActionSubject
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\SavedFilter>
     */
    public function savedFilters(): MorphMany;

    public function canSaveFilters(): bool;
}
