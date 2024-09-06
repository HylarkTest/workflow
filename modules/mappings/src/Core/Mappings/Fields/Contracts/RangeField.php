<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Contracts;

use Illuminate\Validation\Validator;

/**
 * @mixin \Mappings\Core\Mappings\Fields\Field
 */
interface RangeField
{
    public function isRange(): bool;

    public function greaterThanValidationMessage(string $attribute, mixed $otherValue): string;

    public function validateGreaterThan(mixed $value, mixed $otherValue, string $otherField, string $attribute, Validator $validator): bool;
}
