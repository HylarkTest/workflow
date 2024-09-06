<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\FieldResolver;
use Mappings\Core\Mappings\Fields\Types\ImageField;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mappings\Models\Relationships\CustomRelationship;
use Mappings\Core\Mappings\Relationships\Relationship;
use Mappings\Core\Mappings\Fields\Contracts\CustomSortable;

/**
 * Class Item
 *
 * @property int $id
 * @property array $data
 * @property int $mapping_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 *
 * Relationships
 * @property \Mappings\Models\Mapping $mapping
 *
 * @phpstan-import-type ImageValue from \Mappings\Core\Mappings\Fields\Types\ImageField
 * @phpstan-import-type FieldArray from \Mappings\Core\Mappings\Fields\Field
 */
class Item extends Model
{
    protected $with = ['mapping'];

    /**
     * @return string[]
     */
    public function getFillable()
    {
        return array_merge(
            parent::getFillable(),
            ['data'],
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Mappings\Models\Mapping, \Mappings\Models\Item>
     */
    public function mapping(): BelongsTo
    {
        return $this->belongsTo(config('mappings.models.mapping'));
    }

    /**
     * Get the casts array.
     *
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'data' => 'json',
        ]);
    }

    public function resolvePrimaryName(): ?string
    {
        /** @var \Mappings\Core\Mappings\Fields\Types\SystemNameField $primaryField */
        $primaryField = $this->mapping->fieldsOfType(FieldType::SYSTEM_NAME())->first();

        return $this->valueDataForField($primaryField);
    }

    /**
     * @return null|ImageValue
     */
    public function resolvePrimaryImage(): mixed
    {
        $imageFields = $this->mapping->fieldsOfType(FieldType::IMAGE())
            ->where('options.list', '!==', true);
        $primaryImageField = $imageFields->where(fn (ImageField $field) => $field->option('primary'))
            ->first(null, $imageFields->first());

        return $primaryImageField ? $this->valueDataForField($primaryImageField) : null;
    }

    /**
     * @return array<int, array{
     *     fieldId: string,
     *     name: string,
     *     type: \Mappings\Core\Mappings\Fields\FieldType,
     *     value: callable(): string
     * }>
     */
    public function resolveNameFieldValues(): array
    {
        $fields = $this->mapping
            ->fieldsOfType([FieldType::SYSTEM_NAME(), FieldType::NAME()])
            ->filter(fn (Field $field) => ! $field->isList());

        return $fields->toBase()->map(function (Field $field) {
            return [
                'fieldId' => $field->id(),
                'name' => $field->name,
                'type' => $field->type()->value,
                'value' => fn () => $this->valueDataForField($field),
            ];
        })->all();
    }

    /**
     * @return array<int, array{
     *     fieldId: string,
     *     name: string,
     *     type: \Mappings\Core\Mappings\Fields\FieldType,
     *     value: callable(): ImageValue
     * }>
     */
    public function resolveImageFieldValues(): array
    {
        $fields = $this->mapping->fieldsOfType(FieldType::IMAGE())
            ->where('options.list', '!==', true);

        return $fields->toBase()->map(function (Field $field) {
            return [
                'fieldId' => $field->id(),
                'name' => $field->name,
                'type' => $field->type()->value,
                'value' => fn () => $this->valueDataForField($field),
            ];
        })->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function resolveAllEmails(): Collection
    {
        $fields = $this->mapping->fieldsOfType(FieldType::EMAIL());

        return $fields->toBase()->map(function (Field $field) {
            return $this->valueDataForField($field);
        })->flatten()->filter();
    }

    /**
     * @return array{
     *     id: string,
     *     apiName: string,
     *     type: string,
     *     name: string,
     *     options: array<string, mixed>,
     *     meta: array<string, mixed>|null,
     *     section: string|null,
     *     createdAt: string,
     *     updatedAt: string,
     *     fieldId: string,
     *     value: string
     * }
     */
    public function resolvePrimaryNameField(): array
    {
        /** @var \Mappings\Core\Mappings\Fields\Types\SystemNameField $primaryField */
        $primaryField = $this->mapping->fieldsOfType(FieldType::SYSTEM_NAME())->first();
        $info = $primaryField->toArray();
        $info['value'] = $this->dataForField($primaryField);
        $info['fieldId'] = $info['id'];

        return $info;
    }

    /**
     * @return array{
     *     id: string,
     *     apiName: string,
     *     type: string,
     *     name: string,
     *     options: array<string, mixed>,
     *     meta: array<string, mixed>|null,
     *     section: string|null,
     *     createdAt: string,
     *     updatedAt: string,
     *     fieldId: string,
     *     value: ImageValue
     * }|null
     */
    public function resolvePrimaryImageField(): ?array
    {
        $primaryImageField = $this->mapping->fieldsOfType(FieldType::IMAGE())
            ->where(fn (ImageField $field) => $field->option('primary'))
            ->first();

        if (! $primaryImageField) {
            return null;
        }
        $fieldInfo = $primaryImageField->toArray();
        $fieldInfo['value'] = $this->dataForField($primaryImageField);
        $fieldInfo['fieldId'] = $fieldInfo['id'];

        return $fieldInfo;
    }

    public function resolveItemDataAttributes(): FieldResolver
    {
        return new FieldResolver($this->data, $this->mapping->fields);
    }

    public function dataForField(Field $field): mixed
    {
        return $field->resolveValue($this->data[$field->id()] ?? null, []);
    }

    public function valueDataForField(Field $field): mixed
    {
        return $field->resolveNestedDataValue($this->data[$field->id()] ?? null, []);
    }

    public function searchableDataForField(Field $field): mixed
    {
        return $field->toSearchable($this->data[$field->id] ?? null);
    }

    public function sortableDataForField(Field $field): mixed
    {
        if ($field instanceof CustomSortable && $field->canBeSorted()) {
            return $field->toSortable($this->data[$field->id] ?? null);
        }

        return null;
    }

    public function relatedItems(Relationship $relationship): CustomRelationship
    {
        return (new CustomRelationship($this, $relationship))->withTimestamps();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  bool  $exists
     * @return \Mappings\Models\Item
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $item = parent::newInstance($attributes, $exists);
        if ($this->relationLoaded('mapping')) {
            $item->setRelation('mapping', $this->mapping);
        }

        return $item;
    }

    /**
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'relatedItems__') && $this->relationLoaded('mapping')) {
            $relationship = $this->mapping->relationships->firstWhere('apiName', substr($method, 14));
            if ($relationship) {
                return $this->relatedItems($relationship);
            }
        }

        return parent::__call($method, $parameters);
    }
}
