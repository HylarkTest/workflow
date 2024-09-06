<?php

declare(strict_types=1);

namespace Color;

use Color\Rules\ColorRule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Factory as ValidatorFactory;

class ColorServiceProvider extends ServiceProvider
{
    public function boot(ValidatorFactory $validator, Translator $translator): void
    {
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/color'),
        ]);

        $validator->extend(
            'color',
            function (string $attribute, mixed $value, array $parameters) {
                $format = isset($parameters[0]) ? ColorFormat::from(mb_strtoupper($parameters[0])) : null;

                return (new ColorRule($format))->passes($attribute, $value);
            },
            $translator->get('color::validation.color'),
        );
    }
}
