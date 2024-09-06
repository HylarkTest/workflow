<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class RatingField extends Field
{
    public static string $type = 'RATING';

    public string $graphQLType = 'Rating';

    public string $graphQLInputType = 'Float';

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(
            parent::optionRules($data),
            ['rules.max' => ['integer', 'max:20']]
        );
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate),
            'numeric',
            'max:'.$this->max(),
        ];
    }

    /**
     * @param  int  $value
     * @return array|null
     */
    public function resolveSingleValue($value, array $args)
    {
        return $value ? [
            'stars' => $value,
            'max' => $this->max(),
        ] : null;
    }

    public function max(): int
    {
        return $this->option('rules.max', 5);
    }

    public function toSearchable(mixed $data): mixed
    {
        $value = $this->resolveNestedDataValue($data, []);

        $valueCb = fn ($val) => $val ? $val['stars'] : 0;

        return $this->isList() ? array_map($valueCb, $value ?? []) : $valueCb($value);
    }
}
