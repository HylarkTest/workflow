<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Mappings\Core\Mappings\Fields\FieldType;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @extends \Illuminate\Contracts\Support\Arrayable<string, mixed>
 *
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
interface Field extends Arrayable, AttributeCollectionItem
{
    public function id(): string;

    public function type(): FieldType;

    public function fieldName(): string;

    /**
     * @param  array<string, mixed>  $args
     */
    public function resolveValue(mixed $value, array $args): mixed;

    public function serializeValue(mixed $value, mixed $originalValue = null): mixed;

    /**
     * The full GraphQL definition of this field that will be added to any type
     * object that uses it.
     */
    public function graphQLDefinition(string $prefix): array;

    /**
     * The full GraphQL definition for input type objects.
     */
    public function graphQLInputDefinition(string $prefix): array;

    /**
     * The value of the field enum.
     */
    public static function enum(): string;

    /**
     * The rules that should be used to validate the options passed to the rule.
     *
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array;

    public function resolveOptions(): array;

    public function rules(bool $isCreate): array;

    /**
     * @return array<string, string>
     */
    public function messages(): array;

    /**
     * @return array<string, string>
     */
    public function attributes(): array;

    public function optionAttributes(array $data): array;

    public function canRemove(): bool;

    public function cannotRemove(): ?string;

    public function getSection(): ?string;

    public function setSection(?string $section): void;

    public function toSearchable(mixed $data): mixed;
}
