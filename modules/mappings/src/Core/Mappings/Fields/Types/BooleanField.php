<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Concerns\CanBeFilteredByPages;

class BooleanField extends Field
{
    use CanBeFilteredByPages;

    public string $graphQLType = 'Boolean';

    public string $graphQLInputType = 'Boolean';

    public static string $type = 'BOOLEAN';

    public function resolveSingleValue($value, array $args): bool
    {
        return (bool) $value;
    }

    public function toSearchable(mixed $data): mixed
    {
        $value = $this->resolveNestedDataValue($data, []);

        return $value ?? false;
    }

    public function prepareForSerialization($item, $originalValue = null): ?bool
    {
        if ($item === null) {
            return null;
        }

        return (bool) $item;
    }
}
