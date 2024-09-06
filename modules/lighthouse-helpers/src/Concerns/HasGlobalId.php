<?php

declare(strict_types=1);

namespace LighthouseHelpers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use LighthouseHelpers\Core\GlobalIdScope;

/**
 * Trait HasGlobalId
 *
 * @mixin Model
 *
 * @property string $global_id
 */
trait HasGlobalId
{
    public function globalId(): string
    {
        return HasGlobalId::getGlobalIdService()->encode($this->typeName(), $this->getKey());
    }

    public static function convertToGlobalId(int|string $id): string
    {
        /** @phpstan-ignore-next-line static is necessary */
        return HasGlobalId::getGlobalIdService()->encode((new static)->typeName(), $id);
    }

    public function getGlobalIdAttribute(): string
    {
        return $this->globalId();
    }

    public static function getGlobalIdService(): GlobalId
    {
        return resolve(GlobalId::class);
    }

    public static function bootHasGlobalId(): void
    {
        self::addGlobalScope(new GlobalIdScope);
    }

    public function typeName(): string
    {
        return class_basename($this);
    }
}
