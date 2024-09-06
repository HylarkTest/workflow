<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

use Actions\Models\Action;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait PerformsActions
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * Relationships
 *
 * @property \Illuminate\Database\Eloquent\Collection<\Actions\Models\Action> $actionsPerformed
 */
trait PerformsActions
{
    use HasActionEvents;

    public static function bootPerformsActions(): void
    {
        static::getActionEventManager()->listenForCascades(static::class, $isSubject = false);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function actionsPerformed(): MorphMany
    {
        return $this->morphMany(Action::class, 'performer');
    }

    public function getActionPerformerName(): ?string
    {
        if (isset($this->performerDisplayNameKey)) {
            return $this->{$this->performerDisplayNameKey};
        }

        return null;
    }
}
