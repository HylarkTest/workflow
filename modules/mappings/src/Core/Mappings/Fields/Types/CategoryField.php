<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Models\Category;
use Mappings\Models\CategoryItem;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Contracts\Translation\Translator;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Mappings\Core\Categories\CategoryItemBatchLoader;
use Mappings\Core\Mappings\Fields\Concerns\HasMultiSelect;
use Mappings\Core\Mappings\Fields\Contracts\CustomSortable;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class CategoryField extends Field implements CustomSortable, MultiSelectField
{
    use HasMultiSelect;

    public static string $type = 'CATEGORY';

    public string $graphQLType = 'CategoryItem';

    public string $graphQLInputType = 'ID';

    protected GlobalId $globalId;

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, GlobalId $globalId)
    {
        if (isset($field['options']['category']) && ! is_numeric($field['options']['category'])) {
            [$type, $id] = $globalId->decode($field['options']['category']);
            if ($type !== 'Category') {
                throw new \InvalidArgumentException("The category global ID [[{$field['options']['category']}]] must be a category");
            }
            $field['options']['category'] = (int) $id;
        }
        parent::__construct($field, $translator);
        $this->globalId = $globalId;
    }

    public function resolveOptions(): array
    {
        $options = parent::resolveOptions();
        if (is_numeric($options['category'] ?? null)) {
            $options['category'] = $this->globalId->encode(class_basename(Category::class), (int) $options['category']);
        }

        return $options;
    }

    /**
     * @param  string  $value
     * @param  array  $args
     * @return \GraphQL\Deferred
     *
     * @throws \Exception
     */
    public function resolveSuperSingleValue($value, $args)
    {
        return CategoryItemBatchLoader::instanceForItem((string) $value, (int) $this->option('category'));
    }

    /**
     * @param  string  $value
     * @param  ?string  $originalValue
     */
    public function serializeSuperSingleValue($value, $originalValue = null): string
    {
        return $this->globalId->decodeID($value);
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = parent::fieldValueRules($isCreate);

        $rules[] = function ($attribute, $value, $fail) {
            if (! $this->isMultiSelect()) {
                $value = $value ? [$value] : [];
            }
            foreach ($value as $item) {
                [$type, $id] = $this->globalId->decode($item);
                if ($type !== 'CategoryItem' || $this->category()?->items()->whereKey($id)->doesntExist()) {
                    return $fail($this->translator->get('validation.exists', ['attribute' => "\"$this->name\""]));
                }
            }

            return null;
        };

        if ($this->isMultiSelect()) {
            $rules[] = 'array';
        }

        return $rules;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(parent::optionRules($data), [
            'category' => ['required', function ($attribute, $value, $fail) {
                try {
                    $value = $this->globalId->decode($value);
                    [$type, $id] = $value;
                } catch (GlobalIdException) {
                    $type = null;
                    $id = null;
                }
                if ($type !== 'Category' || Category::query()->whereKey($id)->doesntExist()) {
                    return $fail($this->translator->get('validation.exists'));
                }

                return null;
            }],
            'multiSelect' => 'bool',
        ]);
    }

    public function category(): ?Category
    {
        /** @var \Mappings\Models\Category|null $category */
        $category = Category::query()->find($this->option('category'));

        return $category;
    }

    public function toSearchable(mixed $data): mixed
    {
        if ($data && $this->isList()) {
            return array_map(fn ($item) => $this->getNestedDataValue($item), $this->getNestedDataValue($data) ?: []);
        }

        return $this->getNestedDataValue($data);
    }

    public function toSortable(mixed $data): mixed
    {
        $data = $this->getNestedDataValue($data);
        if ($data && ! \is_array($data)) {
            /** @var \Mappings\Models\CategoryItem|null $item */
            $item = CategoryItem::query()->find($data);
            if ($item) {
                return $item->name;
            }
        }

        return null;
    }
}
