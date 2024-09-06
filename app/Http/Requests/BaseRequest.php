<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\BaseType;
use Color\Rules\ColorRule;
use App\Core\Pages\PageType;
use Markers\Core\MarkerType;
use Illuminate\Validation\Rule;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Validation\Rules\Enum;
use Mappings\Core\Mappings\MappingType;
use Illuminate\Foundation\Http\FormRequest;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Contracts\Validation\Validator;
use App\Core\Mappings\Features\MappingFeatureType;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class BaseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = $this->baseRules();

        $this->addFieldRules($rules, $this->input());

        return $rules;
    }

    public function baseData(): array
    {
        $validated = $this->validated();
        $this->sortListArrays($validated);

        return $validated;
    }

    public function attributes(): array
    {
        $rules = $this->rules();
        /** @var string[] $keys */
        $keys = array_keys($rules);
        /** @var string[] $fieldKeys */
        $fieldKeys = preg_grep('/(\d+\.)?spaces\.\d+\.pages\.\d+\.fields\.\d+\./', $keys);
        $attributes = [];
        /** @var array $attributeTranslations */
        $attributeTranslations = __('validation.attributes');
        foreach ($fieldKeys as $key) {
            $translationKey = preg_replace('/\d+\./', '*.', $key);
            if ($translationKey && isset($attributeTranslations[$translationKey])) {
                $attributes[$key] = $attributeTranslations[$translationKey];
            }
        }

        return $attributes;
    }

    protected function sortListArrays(array &$data): void
    {
        if (collect($data)->keys()->every(fn ($k) => is_numeric($k))) {
            ksort($data);
        }

        foreach ($data as &$value) {
            if (\is_array($value)) {
                $this->sortListArrays($value);
            }
        }
    }

    protected function baseRules(): array
    {
        $shortStringRules = ['string', 'max:255'];
        $distinctShortStringRules = ['distinct_shallow', ...$shortStringRules];
        $requiredStringRules = ['required', ...$distinctShortStringRules];
        $longStringRules = ['string', 'max:1023'];
        $viewTypes = ['TILE', 'SPREADSHEET', 'LINE', 'KANBAN'];
        $dataTypes = ['FIELDS', 'RELATIONSHIPS', 'MARKERS', 'SYSTEM', 'FEATURES'];

        return [
            'baseType' => [new Enum(BaseType::class)],
            'name' => $shortStringRules,
            'image' => 'nullable|file|max:2048',
            'markerGroups' => 'array',
            // Used later to reference the groups but will be replaced by a
            // unique ID afterwards.
            'markerGroups.*.id' => $distinctShortStringRules,
            'markerGroups.*.templateRefs' => 'array',
            'markerGroups.*.templateRefs.*' => $shortStringRules,
            'markerGroups.*.name' => $requiredStringRules,
            'markerGroups.*.description' => $longStringRules,
            'markerGroups.*.type' => [new Enum(MarkerType::class)],
            'markerGroups.*.markers' => 'required|array',
            'markerGroups.*.markers.*.id' => $distinctShortStringRules,
            'markerGroups.*.markers.*.name' => $requiredStringRules,
            'markerGroups.*.markers.*.color' => [new ColorRule],
            'categories' => 'array',
            // Used later to reference the categories but will be replaced by a
            // unique ID afterwards.
            'categories.*.id' => $distinctShortStringRules,
            'categories.*.templateRefs' => 'array',
            'categories.*.templateRefs.*' => $shortStringRules,
            'categories.*.name' => $requiredStringRules,
            'categories.*.description' => $longStringRules,
            'categories.*.items' => 'required|array',
            'categories.*.items.*.name' => $requiredStringRules,
            'spaces' => 'array',
            'spaces.*.name' => ['string', 'max:50', 'required', 'distinct_shallow'],
            'spaces.*.description' => $longStringRules,
            'spaces.*.lists.attachments' => ['array'],
            'spaces.*.lists.todos' => ['array'],
            'spaces.*.lists.events' => ['array'],
            'spaces.*.lists.calendar' => ['array'],
            'spaces.*.lists.pinboard' => ['array'],
            'spaces.*.lists.links' => ['array'],
            'spaces.*.lists.notes' => ['array'],
            'spaces.*.lists.*.*.id' => $requiredStringRules,
            'spaces.*.lists.*.*.templateRefs' => 'array',
            'spaces.*.lists.*.*.templateRefs.*' => $shortStringRules,
            'spaces.*.lists.*.*.color' => [new ColorRule],
            'spaces.*.lists.*.*.name' => $requiredStringRules,
            'spaces.*.pages' => 'array',
            // Used later to reference the pages but will be replaced by a
            // unique ID afterward.
            'spaces.*.pages.*.id' => $shortStringRules,
            'spaces.*.pages.*.templateRefs' => 'array',
            'spaces.*.pages.*.templateRefs.*' => $shortStringRules,
            'spaces.*.pages.*.name' => $shortStringRules,
            'spaces.*.pages.*.pageName' => $shortStringRules,
            'spaces.*.pages.*.folder' => ['nullable', ...$shortStringRules],
            'spaces.*.pages.*.description' => $longStringRules,
            'spaces.*.pages.*.symbol' => ['string', 'max:255'],
            'spaces.*.pages.*.pageType' => [new Enum(PageType::class)],
            'spaces.*.pages.*.type' => [new Enum(MappingType::class)],
            'spaces.*.pages.*.singularName' => ['nullable', ...$shortStringRules],
            'spaces.*.pages.*.fields' => [
                'array',
                function (string $attribute, $value, \Closure $fail) {
                    /** @var array<int, array<string, mixed>> $value */
                    return collect($value)->contains('type', FieldType::SYSTEM_NAME()) ?
                        null :
                        $fail(__('validation.name_field_required'));
                },
            ],
            'spaces.*.pages.*.fields.*.id' => ['string', 'max:63'],
            'spaces.*.pages.*.fields.*.nameKey' => ['string', 'max:63'],
            'spaces.*.pages.*.relationships' => 'array',
            'spaces.*.pages.*.relationships.*.name' => $distinctShortStringRules,
            'spaces.*.pages.*.relationships.*.type' => ['required', new Enum(RelationshipType::class)],
            'spaces.*.pages.*.relationships.*.to' => 'required|string',
            'spaces.*.pages.*.relationships.*.inverseName' => $shortStringRules,
            // 'spaces.*.pages.*.relationships.*.markerGroup' => 'string',
            'spaces.*.pages.*.features' => 'array',
            'spaces.*.pages.*.features.*.val' => ['required', new Enum(MappingFeatureType::class)],
            'spaces.*.pages.*.features.*.options' => 'array',
            'spaces.*.pages.*.markerGroups' => 'array',
            'spaces.*.pages.*.markerGroups.*' => 'string',
            'spaces.*.pages.*.examples' => ['array'],
            'spaces.*.pages.*.newFields' => ['array'],
            'spaces.*.pages.*.views' => ['array'],
            'spaces.*.pages.*.views.*.name' => ['required', 'string', 'max:50'],
            'spaces.*.pages.*.views.*.id' => ['required', 'string', 'max:20'],
            'spaces.*.pages.*.views.*.viewType' => ['required', Rule::in($viewTypes)],
            'spaces.*.pages.*.views.*.visibleData' => ['array'],
            'spaces.*.pages.*.views.*.template' => ['string'],
            'spaces.*.pages.*.views.*.visibleData.*.dataType' => ['required', Rule::in($dataTypes)],
            'spaces.*.pages.*.views.*.visibleData.*.slot' => ['nullable', 'string'],
            'spaces.*.pages.*.views.*.visibleData.*.combo' => ['nullable', 'int'],
            'spaces.*.pages.*.views.*.visibleData.*.designAdditional' => ['nullable'],
            'spaces.*.pages.*.views.*.visibleData.*.width' => ['nullable', 'int'],
            'spaces.*.pages.*.views.*.visibleData.*.formattedId' => ['required', 'string'],
            'spaces.*.pages.*.defaultView' => ['string'],
            'spaces.*.pages.*.subset.filter' => [],
            'spaces.*.pages.*.subset.filter.comparator' => ['in:IS,IS_NOT'],
            'spaces.*.pages.*.subset.filter.type' => ['in:MARKER,FIELD'],
            'spaces.*.pages.*.subset.filter.id' => [],
            'spaces.*.pages.*.subset.filter.val' => [],
            'spaces.*.pages.*.subset.mainId' => [],
            'spaces.*.pages.*.lists' => ['array'],
        ];
    }

    protected function addFieldRules(array &$rules, array $baseData, string $prefix = ''): void
    {
        foreach ($baseData['spaces'] ?? [] as $spaceIndex => $space) {
            foreach ($space['pages'] ?? [] as $pageIndex => $page) {
                foreach ($page['fields'] ?? [] as $fieldIndex => $field) {
                    $ruleKey = "{$prefix}spaces.$spaceIndex.pages.$pageIndex.fields.$fieldIndex";

                    $baseRules = [
                        'apiName' => 'string|max:63|filled|api_name|distinct_shallow',
                        'type' => ['required', new EnumValue(FieldType::class)],
                        'section' => 'string|max:63',
                        'name' => 'string|max:63|required|distinct_shallow',
                        'meta' => 'nullable|array',
                    ];

                    foreach ($baseRules as $key => $rule) {
                        $rules["$ruleKey.$key"] = $rule;
                    }

                    if ($type = $field['type'] ?? false) {
                        /** @var array<string, array<int, string|callable|\Illuminate\Validation\Rule>> $optionRules */
                        $optionRules = resolve(FieldType::fieldClass(FieldType::fromValue($type)), ['field' => []])->optionRules($field);
                        collect($optionRules)->each(function ($ruleSet, $key) use (&$rules, $ruleKey, $prefix) {
                            if (str_ends_with($key, 'category')) {
                                /** @var callable $existsRule */
                                $existsRule = $ruleSet[1];
                                $ruleSet[1] = function ($attribute, $value, $fail) use ($existsRule, $prefix) {
                                    /** @var array<int, array<string, mixed>> $categories */
                                    $categories = $this->input("{$prefix}categories");
                                    if (collect($categories)->contains('id', $value)) {
                                        return null;
                                    }

                                    return $existsRule($attribute, $value, $fail);
                                };
                            }
                            $rules["$ruleKey.options.$key"] = $ruleSet;
                        });
                    }
                }
            }
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        report('Validation exception for registration: '.json_encode($validator->errors()));
        parent::failedValidation($validator);
    }
}
