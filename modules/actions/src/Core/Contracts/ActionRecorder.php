<?php

declare(strict_types=1);

namespace Actions\Core\Contracts;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Illuminate\Database\Eloquent\Model;

interface ActionRecorder
{
    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function record(Model $model, ?Model $performer = null, bool $force = false): Action|array|null;

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function recordEvent(string $event, Model $model, ?Model $performer = null, bool $force = false): Action|array|null;

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function recordType(ActionType $type, Model $model, ?Model $performer = null, bool $force = false): Action|array|null;

    public function recordWithPayload(Model $model, ActionType $type, array $payload, ?Model $performer = null, bool $force = false): Action|array|null;

    /**
     * @param  \Closure(\Illuminate\Database\Eloquent\Model): ?\Illuminate\Database\Eloquent\Model  $resolver
     * @return $this
     */
    public function setUserResolver(\Closure $resolver): static;

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Model): ?\Illuminate\Database\Eloquent\Model
     */
    public function getUserResolver(): \Closure;

    public function resolveUser(Model $subject): ?Model;

    public function getType(Model $model): ActionType;
}
