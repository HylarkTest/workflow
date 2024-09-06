<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Concerns\HasUniqueNames;
use Mappings\Core\Mappings\Fields\Types\MultiField;
use LaravelUtils\Database\Eloquent\AttributeCollection;
use Mappings\Core\Mappings\Fields\Contracts\DynamicTypeField;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @extends \LaravelUtils\Database\Eloquent\AttributeCollection<int, \Mappings\Core\Mappings\Fields\Field>
 */
class FieldCollection extends AttributeCollection
{
    use HasUniqueNames;

    /**
     * @param  array<int, array<string, mixed>>|\Mappings\Core\Mappings\Fields\FieldCollection  $items
     */
    public static function makeFromAttribute($items, Model $model): self
    {
        return self::makeCollection($items);
    }

    /**
     * @param  array<int, array>|\Mappings\Core\Mappings\Fields\FieldCollection  $items
     */
    public static function makeCollection($items = []): self
    {
        if ($items instanceof static) {
            return $items;
        }
        $fields = [];

        /** @var array $item */
        foreach ((array) $items as $item) {
            $type = $item['type'] instanceof FieldType ?
                $item['type'] :
                FieldType::fromValue($item['type']);

            $fields[] = $type->newField($item);
        }

        return new self($fields);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function graphQLData(string $prefix): Collection
    {
        /** @phpstan-ignore-next-line This returns the correct type */
        return $this->mapWithKeys(fn (Field $field) => [$field->fieldName() => $field->graphQLDefinition($prefix)]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function graphQLInputFields(string $prefix): Collection
    {
        /** @phpstan-ignore-next-line This returns the correct type */
        return $this->mapWithKeys(fn (Field $field) => [$field->fieldName() => $field->graphQLInputDefinition($prefix)]);
    }

    public function registerDynamicTypes(string $prefix): void
    {
        /** @var \Illuminate\Support\Collection<int, \Mappings\Core\Mappings\Fields\Contracts\DynamicTypeField> $dynamicFields */
        $dynamicFields = $this->filter(fn (Field $field): bool => $field instanceof DynamicTypeField);

        $dynamicFields->each(fn (DynamicTypeField $field) => $field->registerDynamicFields($prefix));
    }

    public function addItem(array $args, Model $model): AttributeCollectionItem
    {
        $field = $args['type']
            ->newField($args);

        $field->apiName = $this->getUniqueName($field->apiName);

        if ($field->option('primary')) {
            $this->each(function (Field $existingField) use ($field) {
                if ($existingField->option('primary') && $existingField->type()->is($field->type())) {
                    unset($existingField->options['primary']);
                }
            });
        }

        $this->push($field);

        return $field;
    }

    /**
     * @param  string  $id
     */
    public function changeItem($id, array $args, Model $model): ?AttributeCollectionItem
    {
        $originalKey = $this->findIndex($id);

        if ($originalKey === false) {
            return null;
        }

        /** @var \Mappings\Core\Mappings\Fields\Field $field */
        $field = $this[$originalKey];

        $originalArray = $field->toArray();

        $field->name = $args['name'] ?? $field->name;
        $field->apiName = $args['apiName'] ?? $field->apiName;
        $field->updateOptions($args['options'] ?? []);
        $field->meta = (array) ($args['meta'] ?? $field->meta);
        $field->section = \array_key_exists('section', $args) && $args['section'] !== '' ?
            $args['section'] :
            $field->section;

        if (! ($originalArray['options']['primary'] ?? false) && $field->option('primary')) {
            $this->each(function (Field $existingField) use ($field) {
                if (
                    $existingField->option('primary')
                    && $existingField->id() !== $field->id()
                    && $existingField->type()->is($field->type())
                ) {
                    unset($existingField->options['primary']);
                }
            });
        }

        if ($originalArray !== $field->toArray()) {
            $field->updatedAt = (string) Carbon::now();
        }

        return $field;
    }

    /**
     * @param  string  $id
     */
    public function removeItem($id, Model $model): ?AttributeCollectionItem
    {
        return $this->forgetItem($id);
    }

    public function fullCount(): int
    {
        [$multiFields, $otherFields] = $this->partition(fn (Field $field) => $field->type()->is(FieldType::MULTI()));

        return $otherFields->count() + $multiFields->sum(fn (MultiField $field) => $field->fields()->fullCount());
    }

    public function hasField(string $fieldIdKey): bool
    {
        $fieldIds = explode('.', $fieldIdKey);
        $fieldId = array_shift($fieldIds);
        $field = $this->find($fieldId);
        if (! $field) {
            return false;
        }
        if ($field instanceof MultiField && count($fieldIds) > 0) {
            return $field->fields()->hasField(implode('.', $fieldIds));
        }

        return true;
    }

    public function getField(string $fieldIdKey): ?Field
    {
        $fieldIds = explode('.', $fieldIdKey);
        $fieldId = array_shift($fieldIds);
        $field = $this->find($fieldId);
        if (! $field) {
            return null;
        }
        if ($field instanceof MultiField && count($fieldIds) > 0) {
            return $field->fields()->getField(implode('.', $fieldIds));
        }

        return $field;
    }
}
