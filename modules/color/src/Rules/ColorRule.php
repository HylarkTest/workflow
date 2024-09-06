<?php

declare(strict_types=1);

namespace Color\Rules;

use Color\Color;
use Color\ColorFormat;
use Illuminate\Contracts\Validation\Rule;

class ColorRule implements Rule
{
    public function __construct(protected ?ColorFormat $format = null) {}

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->format) {
            return match ($this->format) {
                ColorFormat::HEX => Color::isHex($value),
                ColorFormat::RGB => Color::isRgb($value),
                ColorFormat::HSL => Color::isHsl($value),
            };
        }
        try {
            Color::make($value);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function message()
    {
        if ($this->format) {
            return __('color::validation.color_strict', ['format' => $this->format->value]);
        }

        return __('color::validation.color');
    }
}
