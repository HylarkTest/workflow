<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use App\Models\Page;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Concerns\HasMultiSelect;
use Mappings\Core\Mappings\Fields\Contracts\CustomSortable;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;
use Mappings\Core\Mappings\Fields\Concerns\CanBeFilteredByPages;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class SelectField extends Field implements CustomSortable, MultiSelectField
{
    use CanBeFilteredByPages;
    use HasMultiSelect;

    public static string $type = 'SELECT';

    public string $graphQLType = 'ItemSelect';

    public string $graphQLInputType = 'String';

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(
            parent::optionRules($data),
            [
                'multiSelect' => ['boolean'],
                'valueOptions' => [
                    'array',
                    'required',
                    function ($attribute, $value, $fail) {
                        $id = $this->id();
                        if (! $id) {
                            return;
                        }
                        /** @var array<string, string> $oldValueOptions */
                        $oldValueOptions = $this->option('valueOptions');
                        $missingValueOptions = collect($oldValueOptions)->diffKeys($value);
                        $pagesFilteringByField = $this->usedByPages()->filter(function (Page $page) use ($missingValueOptions): bool {
                            return collect($page->fieldFilters)
                                ->contains(function ($filter) use ($missingValueOptions) {
                                    return $missingValueOptions->keys()->contains(json_decode($filter['match']));
                                });
                        });

                        if ($pagesFilteringByField->isNotEmpty()) {
                            $message = 'This field is used to filter pages. Please remove it from the pages first. Page(s): "'.$pagesFilteringByField->implode('name', '", "').'"';

                            return $fail($message);
                        }
                    },
                ],
                'valueOptions.*' => ['string', 'max:40', 'required'],
            ]
        );
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = parent::fieldValueRules($isCreate);
        if ($this->isMultiSelect()) {
            return $rules;
        }

        return [
            ...$rules,
            ...$this->selectOptionRules(),
        ];
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $rules = parent::fieldValueSubRules($isCreate);

        return [
            ...$rules,
            '*' => $this->selectOptionRules(),
        ];
    }

    public function toSearchable(mixed $data): mixed
    {
        $data = $this->resolveNestedDataValue($data, []);

        $extractFn = fn ($value) => $this->isMultiSelect() ? Arr::pluck($value ?: [], 'selectKey') : $value['selectKey'] ?? null;

        return $this->isList() ? array_map($extractFn, $data ?: []) : $extractFn($data);
    }

    public function toSortable(mixed $data): mixed
    {
        $data = $this->getNestedDataValue($data);

        return $data !== null ? $this->option("valueOptions.$data") : null;
    }

    protected function selectOptionRules(): array
    {
        return [
            'string',
            Rule::in(array_keys($this->option('valueOptions'))),
        ];
    }

    /**
     * @param  array<int,mixed>  $args
     */
    protected function resolveSuperSingleValue(mixed $key, array $args): mixed
    {
        $value = $this->option('valueOptions.'.$key);
        $key = $value ? $key : null;

        return $value ? [
            'selectKey' => $key,
            'selectValue' => $value,
        ] : null;
    }
}
