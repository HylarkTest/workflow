<?php

declare(strict_types=1);

namespace Actions\Core\Contracts;

interface ActionEventManager
{
    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    public function listenToModelEvents(string $model): void;

    public function listenForCascades(string $model, bool $isSubject = true): void;
}
