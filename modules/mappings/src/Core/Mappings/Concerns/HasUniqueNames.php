<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Concerns;

/**
 * Trait HasUniqueNames
 *
 * @mixin \Illuminate\Support\Collection
 */
trait HasUniqueNames
{
    public function getUniqueName(string $name, string $key = 'apiName'): string
    {
        $alreadyHasName = $this->contains($key, $name);

        while ($alreadyHasName) {
            $name = increment_string_suffix($name);
            $alreadyHasName = $this->contains($key, $name);
        }

        return $name;
    }
}
