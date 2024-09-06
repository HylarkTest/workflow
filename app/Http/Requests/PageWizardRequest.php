<?php

declare(strict_types=1);

namespace App\Http\Requests;

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

class PageWizardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $shortStringRules = ['string', 'max:255'];
        $distinctShortStringRules = ['distinct_shallow', ...$shortStringRules];
        $requiredStringRules = ['required', ...$distinctShortStringRules];
        $longStringRules = ['string', 'max:1023'];
        $viewTypes = ['TILE', 'SPREADSHEET', 'LINE', 'KANBAN'];
        $dataTypes = ['FIELDS', 'RELATIONSHIPS', 'MARKERS', 'SYSTEM', 'FEATURES'];
        $rules = [
            'reusedMarkerGroups' => 'array',
            'reusedMarkerGroups.*' => $shortStringRules,
            'reusedCategories' => 'array',
            'reusedCategories.*' => $shortStringRules,
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
            'space.id' => 'string|required',
            'space.description' => $longStringRules,
            'space.reusedBlueprints' => 'array',
            'space.reusedBlueprints.*' => $shortStringRules,
            'space.lists.attachments' => ['array'],
            'space.lists.todos' => ['array'],
            'space.lists.events' => ['array'],
            'space.lists.pinboard' => ['array'],
            'space.lists.links' => ['array'],
            'space.lists.notes' => ['array'],
            'space.lists.*.*.id' => $requiredStringRules,
            'space.lists.*.*.color' => [new ColorRule],
            'space.lists.*.*.name' => $requiredStringRules,
            'space.pages' => 'array',
            // Used later to reference the pages but will be replaced by a
            // unique ID afterward.
            'space.pages.*.id' => $shortStringRules,
            'space.pages.*.templateRefs' => 'array',
            'space.pages.*.templateRefs.*' => $shortStringRules,
            'space.pages.*.name' => $shortStringRules,
            'space.pages.*.pageName' => $shortStringRules,
            'space.pages.*.folder' => $shortStringRules,
            'space.pages.*.description' => $longStringRules,
            'space.pages.*.symbol' => ['string', 'max:255'],
            'space.pages.*.pageType' => [new Enum(PageType::class)],
            'space.pages.*.type' => [new Enum(MappingType::class)],
            'space.pages.*.singularName' => ['nullable', ...$shortStringRules],
            'space.pages.*.fields' => [
                'array',
                function (string $attribute, $value, \Closure $fail) {
                    /** @var array<int, array<string, mixed>> $value */
                    return collect($value)->contains('type', FieldType::SYSTEM_NAME()) ?
                        null :
                        $fail(__('validation.name_field_required'));
                },
            ],
            'space.pages.*.fields.*.id' => ['string', 'max:63'],
            'space.pages.*.fields.*.nameKey' => ['string', 'max:63'],
            'space.pages.*.relationships' => 'array',
            'space.pages.*.relationships.*.name' => $distinctShortStringRules,
            'space.pages.*.relationships.*.type' => ['required', new Enum(RelationshipType::class)],
            'space.pages.*.relationships.*.to' => 'required|string',
            'space.pages.*.relationships.*.inverseName' => $shortStringRules,
            // 'space.pages.*.relationships.*.markerGroup' => 'string',
            'space.pages.*.features' => 'array',
            'space.pages.*.features.*.val' => ['required', new Enum(MappingFeatureType::class)],
            'space.pages.*.features.*.options' => 'array',
            'space.pages.*.markerGroups' => 'array',
            'space.pages.*.markerGroups.*' => 'string',
            'space.pages.*.examples' => ['array'],
            'space.pages.*.newFields' => ['array'],
            'space.pages.*.views' => ['array'],
            'space.pages.*.views.*.name' => ['required', 'string', 'max:50'],
            'space.pages.*.views.*.id' => ['required', 'string', 'max:20'],
            'space.pages.*.views.*.viewType' => ['required', Rule::in($viewTypes)],
            'space.pages.*.views.*.visibleData' => ['array'],
            'space.pages.*.views.*.template' => ['string'],
            'space.pages.*.views.*.visibleData.*.dataType' => ['required', Rule::in($dataTypes)],
            'space.pages.*.views.*.visibleData.*.slot' => ['nullable', 'string'],
            'space.pages.*.views.*.visibleData.*.combo' => ['nullable', 'int'],
            'space.pages.*.views.*.visibleData.*.designAdditional' => ['nullable'],
            'space.pages.*.views.*.visibleData.*.width' => ['nullable', 'int'],
            'space.pages.*.views.*.visibleData.*.formattedId' => ['required', 'string'],
            'space.pages.*.defaultView' => ['string'],
            'space.pages.*.subset.filter' => [],
            'space.pages.*.subset.filter.comparator' => ['in:IS,IS_NOT'],
            'space.pages.*.subset.filter.type' => ['in:MARKER,FIELD'],
            'space.pages.*.subset.filter.id' => [],
            'space.pages.*.subset.filter.val' => [],
            'space.pages.*.subset.mainId' => [],
            'space.pages.*.lists' => ['array'],
        ];

        foreach ($this->input('space')['pages'] ?? [] as $pageIndex => $page) {
            foreach ($page['fields'] ?? [] as $fieldIndex => $field) {
                $ruleKey = "space.pages.$pageIndex.fields.$fieldIndex";

                $baseRules = [
                    'apiName' => 'string|max:63|filled|api_name|distinct_shallow',
                    'type' => ['required', new EnumValue(FieldType::class)],
                    'section' => 'string|max:63',
                    'name' => 'string|max:63|required|distinct_shallow',
                    'meta' => 'array',
                ];

                foreach ($baseRules as $key => $rule) {
                    $rules["$ruleKey.$key"] = $rule;
                }

                if ($type = $field['type'] ?? false) {
                    /** @var array<string, array<int, string|callable|\Illuminate\Validation\Rule>> $optionRules */
                    $optionRules = resolve(FieldType::fieldClass(FieldType::fromValue($type)), ['field' => []])->optionRules($field);
                    collect($optionRules)->each(function ($ruleSet, $key) use (&$rules, $ruleKey) {
                        if (str_ends_with($key, 'category')) {
                            /** @var callable $existsRule */
                            $existsRule = $ruleSet[1];
                            $ruleSet[1] = function ($attribute, $value, $fail) use ($existsRule) {
                                /** @var array<int, array<string, mixed>> $categories */
                                $categories = $this->input('categories');
                                $reusedCategories = $this->input('reusedCategories', []);
                                if (isset($reusedCategories[$value]) || collect($categories)->contains('id', $value)) {
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

        return $rules;
    }

    public function pageWizardData(): array
    {
        return $this->validated();
    }

    protected function validationMessage(): string
    {
        return 'Validation failed for page wizard';
    }

    protected function failedValidation(Validator $validator): void
    {
        report($this->validationMessage().': '.json_encode($validator->errors()));
        parent::failedValidation($validator);
    }
}
