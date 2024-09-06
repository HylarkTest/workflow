<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\Models\Mapping;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Translation\Translator;

class MappingFieldQuery extends Mutation
{
    protected Translator $translator;

    public function __construct(Factory $validationFactory, Translator $translator)
    {
        parent::__construct($validationFactory);
        $this->translator = $translator;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        if (! $base->accountLimits()->canAddAField($mapping, $args['input'])) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        /** @var \Mappings\Core\Mappings\Fields\Field $field */
        $field = $args['input']['type']->newField([]);
        $rules = $this->fieldRules($field, $mapping, $args);

        $attributes = array_merge_recursive(
            $this->translator->get('mappings::validation.attributes.input'),
            $field->optionAttributes($args['input'])
        );

        $messages = array_merge_recursive(
            $this->translator->get('mappings::validation.field_options'),
            $field->optionMessages($args['input'])
        );

        $data = $this->validate(
            $args,
            $rules,
            $resolveInfo,
            Arr::dot(['input' => $messages]),
            Arr::dot(['input' => $attributes]),
        );

        $mapping->addField($data['input']);

        return $this->mutationResponse(200, 'The field was added successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \LighthouseHelpers\Exceptions\ValidationException
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $id = $args['input']['id'];

        /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
        $field = $mapping->fields->find($id);

        if (! $field) {
            $this->throwValidationException('input.id', ['The field does not exist']);
        }
        if ($reason = $field->cannotRemove()) {
            $this->throwValidationException('input.id', [$reason]);
        }

        $mapping->removeField($id);

        return $this->mutationResponse(200, 'The field was removed successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['mappingId']);

        $id = $args['input']['id'];
        $data = $args;

        /** @var \Mappings\Core\Mappings\Fields\Field $field */
        $field = $mapping->fields->find($id);

        $rules = $this->fieldRules($field, $mapping, $data, $field);
        $rules['input.options.list'] = 'exclude';

        $attributes = array_merge_recursive(
            $this->translator->get('mappings::validation.attributes'),
            $field->optionAttributes($data)
        );

        $data = $this->validate(
            $args,
            $rules,
            $resolveInfo,
            [],
            Arr::dot($attributes)
        )['input'] ?? [];

        $mapping->updateField($id, $data);

        return $this->mutationResponse(200, 'The field was updated successfully', [
            'mapping' => $mapping,
        ]);
    }

    protected function replaceWildcards(string|array|Rule|\Closure|\Stringable $rules): string|array|Rule|\Closure|\Stringable
    {
        if (\is_array($rules)) {
            return array_map([$this, 'replaceWildcards'], $rules);
        }
        if (\is_string($rules) || $rules instanceof \Stringable) {
            return str_replace('{field}', 'input.options', (string) $rules);
        }

        return $rules;
    }

    protected function fieldRules(Field $field, Mapping $mapping, array $data, ?Field $originalField = null): array
    {
        $optionRules = $field->optionRules($data['input']);
        $fieldCollection = $originalField ? $mapping->fields->where('id', '!=', $originalField->id()) : $mapping->fields;

        return array_merge(
            [
                'input.apiName' => ['string', 'max:'.Field::MAX_NAME_LENGTH, 'filled', 'api_name', function ($attribute, $value, $fail) use ($fieldCollection) {
                    if ($fieldCollection->pluck('apiName')
                        ->contains($value)
                    ) {
                        $fail($this->translator->get('validation.unique'));
                    }
                }],
                'input.name' => ['string', 'max:50', 'filled', function ($attribute, $value, $fail) use ($fieldCollection) {
                    /** @var \Illuminate\Support\Collection<int, string> $names */
                    $names = $fieldCollection->pluck('name');
                    if ($names->map(fn (string $name): string => mb_strtolower($name))
                        ->contains(mb_strtolower($value))
                    ) {
                        $fail($this->translator->get('validation.unique'));
                    }
                }],
                'input.type' => [],
                'input.meta' => [],
                'input.section' => 'nullable|string',
            ],
            Collection::make($optionRules)
                ->mapWithKeys(fn ($ruleSet, $key) => [
                    "input.options.$key" => $this->replaceWildcards($ruleSet),
                ])->all()
        );
    }
}
