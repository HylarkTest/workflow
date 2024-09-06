<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationData;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot(ValidationFactory $validationFactory, Translator $translator): void
    {
        $validationFactory->extend(
            'distinct_shallow',
            function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
                /** @var string $arrayKey */
                $arrayKey = preg_replace('/\d+(?=[^\d]*$)/', '*', $attribute);

                $data = $validator->getData();

                $attributeData = ValidationData::extractDataFromPath(
                    ValidationData::getLeadingExplicitAttributePath($arrayKey), $data
                );

                $pattern = str_replace('\*', '[^.]+', preg_quote($arrayKey, '#'));

                $distinctValues = Arr::where(Arr::dot($attributeData), function ($value, $key) use ($pattern) {
                    return (bool) preg_match('#^'.$pattern.'\z#u', $key);
                });

                $values = Arr::except($distinctValues, $attribute);

                if (\in_array('ignore_case', $parameters, true)) {
                    return empty(preg_grep('/^'.preg_quote($value, '/').'$/iu', $values));
                }

                return ! \in_array($value, array_values($values), \in_array('strict', $parameters, true));
            },
            $translator->get('validation.distinct'),
        );

        $validationFactory->extendImplicit(
            'required_unless_filled',
            function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
                $data = $validator->getData();
                $default = new \stdClass;
                $attributeValue = Arr::get($data, $attribute);
                $parameterValue = Arr::get($data, $parameters[0], $default);
                $parameterValueExists = $parameterValue !== $default;
                if ($parameterValueExists && blank($parameterValue)) {
                    return filled($attributeValue);
                }

                return true;
            }
        );
    }
}
