<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface TenantModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Base, \Illuminate\Database\Eloquent\Model>
     */
    public function base(): BelongsTo;
}
