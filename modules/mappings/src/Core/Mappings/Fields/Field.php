<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Mappings\Fields\Concerns\HasGraphQLDefinitions;
use Mappings\Core\Mappings\Fields\Contracts\Field as FieldInterface;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 *
 * @phpstan-type FieldOptions = array{
 *     id?: string,
 *     name?: string,
 *     options?: array<string, mixed>,
 *     meta?: array<string, mixed>,
 *     apiName?: string,
 *     section?: string,
 *     createdAt?: string,
 *     updatedAt?: string,
 * }
 * @phpstan-type FieldArray = array{
 *     id: string,
 *     apiName: string,
 *     type: string,
 *     name: string,
 *     options: array<string, mixed>,
 *     meta: array<string, mixed>|null,
 *     section: string|null,
 *     createdAt: string,
 *     updatedAt: string,
 * }
 */
abstract class Field implements Arrayable, FieldInterface
{
    use HasGraphQLDefinitions;

    public const MAX_NAME_LENGTH = 50;

    public const FIXED_OPTIONS = ['list'];

    public const LIST_VALUE = '_c';

    public const VALUE = '_v';

    public const LABEL = '_l';

    public const IS_MAIN = '_m';

    public static string $type;

    public ?array $options;

    public ?array $meta;

    public string $id;

    public string $name;

    public string $createdAt;

    public string $updatedAt;

    public string $apiName;

    public ?string $section;

    protected Translator $translator;

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator)
    {
        $this->name = $field['name'] ?? $this->type()->value;
        $this->options = $this->parseOptions($field['options'] ?? null);
        $this->meta = isset($field['meta']) ? (array) $field['meta'] : null;
        $this->id = $field['id'] ?? Utils::generateRandomString();
        $this->apiName = $field['apiName'] ?? $this->generateApiName();
        $this->section = $field['section'] ?? null;
        $this->createdAt = $field['createdAt'] ?? (string) Carbon::now();
        $this->updatedAt = $field['updatedAt'] ?? (string) Carbon::now();

        $this->translator = $translator;
    }

    public static function enum(): string
    {
        return static::$type;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): FieldType
    {
        return FieldType::fromValue(static::enum());
    }

    public function options(): array
    {
        return $this->options ?: [];
    }

    public function resolveOptions(): array
    {
        return $this->options();
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->options(), $key, $default);
    }

    public function rule(string $key, mixed $default = null): mixed
    {
        return $this->option("rules.$key", $default);
    }

    /**
     * @return FieldArray
     */
    public function toArray(): array
    {
        $options = $this->options();
        if (($options['labeled']['freeText'] ?? false) === true) {
            unset($options['labeled']['labels']);
        }

        return [
            'id' => $this->id,
            'apiName' => $this->apiName,
            'type' => (string) $this->type(),
            'name' => $this->name,
            'options' => $options,
            'meta' => $this->meta ?: null,
            'section' => $this->section,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function fieldName(): string
    {
        return $this->apiName;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return [
            'rules.required' => 'bool',
            'list' => '',
            'list.max' => 'int',
            'list.oneRequired' => 'bool',
            'labeled.freeText' => 'bool',
            'labeled.labels' => 'array|required_if:{field}.labeled.freeText,false',
            'labeled.labels.*' => 'required_if:{field}.labeled.freeText,false|string|max:100',
        ];
    }

    /**
     * @return array<string, ValidationRule[]>
     */
    public function rules(bool $isCreate): array
    {
        $labelled = $this->isLabeled();
        $freeTextLabeled = $this->isFreetextLabeled();
        $list = $this->isList();

        $fieldValueRules = $this->fieldValueRules($isCreate);
        $fieldValueSubRules = $this->fieldValueSubRules($isCreate);

        $labelRules = $labelled ? [
            'label' => $freeTextLabeled
                ? ['nullable', 'string', 'max:100']
                : [Rule::in(array_keys($this->option('labeled.labels')))],
        ] : [];

        if ($list) {
            $max = $this->option('list.max', false);
            $oneRequired = $this->option('list.oneRequired', false);

            return [
                'listValue' => [
                    'array',
                    ...(\is_int($max) ? ["max:$max"] : []),
                    $oneRequired ? 'min:1' : 'nullable',
                    /** @param null|array{main?: bool} $value */
                    function ($attribute, $value, $fail) {
                        if (is_array($value) && collect($value)->where('main', true)->count() > 1) {
                            $fail($this->translator->get('mappings::validation.rules.single_main'));
                        }
                    },
                ],
                'listValue.*.fieldValue' => $fieldValueRules,
                'listValue.*.main' => ['nullable', 'bool'],
                ...collect($fieldValueSubRules)
                    ->mapWithKeys(fn ($rules, $key) => ["listValue.*.fieldValue.$key" => $rules]),
                ...$labelRules,
            ];
        }

        return [
            'fieldValue' => $fieldValueRules,
            'main' => ['missing'],
            ...collect($fieldValueSubRules)
                ->mapWithKeys(fn ($rules, $key) => ["fieldValue.$key" => $rules]),
            ...$labelRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = $this->singleAttributes();

        if ($this->isList()) {
            $listAttributes = [];
            foreach ($attributes as $key => $attribute) {
                $listAttributes["listValue.*.$key"] = $attribute;
            }

            return [...$attributes, ...$listAttributes];
        }

        return $attributes;
    }

    public function optionAttributes(array $data): array
    {
        return [];
    }

    public function optionMessages(array $data): array
    {
        return [];
    }

    public function canRemove(): bool
    {
        return ! $this->cannotRemove();
    }

    public function cannotRemove(): ?string
    {
        return null;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): void
    {
        $this->section = $section;
    }

    public function isList(): bool
    {
        return (bool) $this->option('list', false);
    }

    public function isLabeled(): bool
    {
        return (bool) $this->option('labeled');
    }

    public function isFreetextLabeled(): bool
    {
        return $this->isLabeled()
            && ($this->option('labeled') === true || $this->option('labeled.freeText'));
    }

    public function toSearchable(mixed $data): mixed
    {
        return $this->resolveNestedDataValue($data, []);
    }

    public function canBeSorted(): bool
    {
        return ! $this->isList()
            && ! $this->option('multiSelect', false)
            && ! $this->option('isRange', false);
    }

    public function updateOptions(array $options = []): void
    {
        $this->options = [
            ...$options,
            ...Arr::only($this->options ?: [], self::FIXED_OPTIONS),
        ];
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $required = $this->rule('required', false);
        $requiredRule = $isCreate ? 'required' : 'filled';

        return [
            $required ? $requiredRule : 'nullable',
        ];
    }

    protected function fieldValueSubRules(bool $isCreate): array
    {
        return [];
    }

    protected function parseOptions(?array $options): ?array
    {
        if (! $options) {
            return null;
        }
        $rules = $this->optionRules($options);
        $dotOptions = Arr::dot($options);
        foreach ($rules as $key => $rule) {
            foreach ($dotOptions as $dotKey => $value) {
                $regex = str_replace('\*', '.*', preg_quote($key, '/'));
                if (preg_match("/^{$regex}$/", $dotKey)) {
                    if ($this->ruleHas('bool', $rule) || $this->ruleHas('boolean', $rule)) {
                        Arr::set($options, $dotKey, (bool) $value);
                    } elseif ($this->ruleHas('int', $rule)) {
                        Arr::set($options, $dotKey, (int) $value);
                    }
                }
            }
        }
        $list = $options['list'] ?? false;
        if ($list === '1') {
            $options['list'] = true;
        } elseif (! \is_array($list) && ! \is_bool($list)) {
            $options['list'] = false;
        }

        return $options;
    }

    protected function ruleHas(string $key, array|string $rules): bool
    {
        if (\is_string($rules)) {
            return str_contains($rules, $key);
        }

        return \in_array($key, $rules, true);
    }

    /**
     * @return array<string, string>
     */
    protected function singleAttributes(): array
    {
        return [
            'listValue' => "\"$this->name\"",
            'fieldValue' => "\"$this->name\"",
            'label' => $this->translator->get('mappings::validation.attributes.label'),
        ];
    }

    protected function generateApiName(): string
    {
        return Utils::generateGraphQLType($this->name);
    }
}
