<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use LighthouseHelpers\Concerns\HasGlobalId;

trait CanBePreviewed
{
    use HasGlobalId {
        globalId as protected baseGlobalId;
    }

    protected bool $isPreview = false;

    public function setIsPreview(bool $isPreview): void
    {
        $this->isPreview = $isPreview;
    }

    public function isPreview(): bool
    {
        return $this->isPreview;
    }

    public function globalId(): string
    {
        if ($this->isPreview()) {
            return static::getGlobalIdService()->encode((new self)->typeName(), (string) Str::uuid());
        }

        return $this->baseGlobalId();
    }
}
