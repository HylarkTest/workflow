<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

/**
 * Trait HasMaxLength
 *
 * @mixin \Mappings\Core\Mappings\Fields\Field
 *
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
trait HasMaxLength
{
    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(static::maxOptionRules(), parent::optionRules($data));
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $parentRules = parent::fieldValueRules($isCreate);

        $parentRules[] = 'max:'.$this->maxLength();

        return $parentRules;
    }

    protected static function maxOptionRules(): array
    {
        $max = static::MAX_LENGTH;

        return ['rules.max' => "integer|max:$max"];
    }

    protected function maxLength(): int
    {
        $max = static::MAX_LENGTH;

        if ($customMax = $this->rule('max')) {
            $max = $customMax < $max ? $customMax : $max;
        }

        return $max;
    }
}
