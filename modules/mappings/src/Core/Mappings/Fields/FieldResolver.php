<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

use ArrayAccess;
use Illuminate\Support\Collection;

/**
 * Class FieldResolver
 *
 * @implements ArrayAccess<string, mixed>
 */
class FieldResolver implements \ArrayAccess
{
    protected array $data;

    /**
     * @var \Illuminate\Support\Collection<string, \Mappings\Core\Mappings\Fields\Field>
     */
    protected Collection $fields;

    public function __construct(array $data, FieldCollection $fields)
    {
        $this->data = $data;
        /** @var \Illuminate\Support\Collection<string, \Mappings\Core\Mappings\Fields\Field> $keyedFields */
        $keyedFields = $fields->keyBy(fn (Field $field) => $field->fieldName());
        $this->fields = $keyedFields;
    }

    public function offsetExists($offset): bool
    {
        return $this->fields->offsetExists($offset);
    }

    public function offsetGet($offset): \Closure
    {
        $field = $this->fields[$offset];
        if (! $field) {
            throw new \InvalidArgumentException('The offset does not exist');
        }

        return fn ($root, array $args) => $field->resolveValue($this->data[$field->id()] ?? null, $args);
    }

    public function offsetSet($offset, $value): void
    {
        $field = $this->fields[$offset];
        if (! $field) {
            throw new \InvalidArgumentException('The offset does not exist');
        }
        $this->data[$offset] = $field->serializeValue($value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
