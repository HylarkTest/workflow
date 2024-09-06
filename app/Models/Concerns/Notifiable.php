<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \App\Models\Model
 */
trait Notifiable
{
    use \Illuminate\Notifications\Notifiable;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\DatabaseNotification>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }
}
