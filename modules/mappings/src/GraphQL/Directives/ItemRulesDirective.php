<?php

declare(strict_types=1);

namespace Mappings\GraphQL\Directives;

use Mappings\Models\Mapping;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgumentSetValidation;

class ItemRulesDirective extends BaseDirective implements ArgDirective, ArgumentSetValidation
{
    protected FieldCollection $fields;

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Validate and resolve the item
"""
directive @itemRules(
  """
  The id of the mapping for the item
  """
  mapping: Int

  create: Boolean

  fields: String
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * @return ValidationRules
     *
     * @throws \JsonException
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    public function rules(): array
    {
        return $this->mapThroughFields(function (Field $field) {
            $rules = $field->rules($this->directiveArgValue('create'));

            $fieldName = $field->fieldName();

            foreach ($rules as $key => $ruleSet) {
                if (\is_array($ruleSet)) {
                    $rules[$key] = array_map(
                        static fn ($rule) => \is_string($rule) ? str_replace('{field}', "data.$fieldName", $rule) : $rule,
                        $ruleSet,
                    );
                }
            }

            return $rules;
        });
    }

    /**
     * @return array<string, string>
     *
     * @throws \JsonException
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    public function messages(): array
    {
        return $this->mapThroughFields(fn (Field $field) => $field->messages());
    }

    /**
     * @return array<string, string>
     *
     * @throws \JsonException
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    public function attributes(): array
    {
        return $this->mapThroughFields(fn (Field $field) => $field->attributes());
    }

    /**
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     * @throws \JsonException
     */
    protected function getFields(): FieldCollection
    {
        if (! isset($this->fields)) {
            $mappingId = $this->directiveArgValue('mapping');
            $fields = $this->directiveArgValue('fields');

            if ($mappingId) {
                /** @var \Mappings\Models\Mapping $mapping */
                $mapping = Mapping::query()->findOrFail($mappingId);
                $this->fields = $mapping->fields;
            } elseif ($fields) {
                $this->fields = FieldCollection::makeCollection(json_decode($fields, true, 512, \JSON_THROW_ON_ERROR));
            } else {
                throw new DirectiveException('Mapping ID or fields array must be specified');
            }
        }

        return $this->fields;
    }

    /**
     * @template T
     *
     * @param  \Closure(\Mappings\Core\Mappings\Fields\Field): array<int|string, T>  $callback
     * @return array<string, T>
     *
     * @throws \JsonException
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     */
    protected function mapThroughFields(\Closure $callback): array
    {
        $results = [];
        $this->getFields()->each(function (Field $field) use ($callback, &$results) {
            $fieldItems = $callback($field);

            $fieldName = $field->fieldName();

            foreach ($fieldItems as $key => $item) {
                if (\is_int($key)) {
                    $results['data.'.$fieldName] = $item;
                } else {
                    $results['data.'.$fieldName.'.'.$key] = $item;
                }
            }
        });

        return $results;
    }
}
