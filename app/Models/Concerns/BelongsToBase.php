<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToBase
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Base>
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }
}
