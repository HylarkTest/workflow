<?php

declare(strict_types=1);

namespace Color;

use PHPStan\ShouldNotHappenException;

abstract class Color
{
    public static function make(mixed $color): self
    {
        return match (true) {
            $color instanceof self => $color,
            static::isHex($color) => new HexColor($color),
            static::isRgb($color) => new RgbColor(...static::extractRgbValues($color)),
            static::isHsl($color) => new HslColor(...static::extractHslValues($color)),
            default => throw new \InvalidArgumentException('The color must be valid hsl or rgb numbers'),
        };
    }

    public function format(ColorFormat $format): self
    {
        return match ($format) {
            ColorFormat::HEX => $this->toHex(),
            ColorFormat::RGB => $this->toRgb(),
            ColorFormat::HSL => $this->toHsl(),
        };
    }

    abstract public function toRgb(): RgbColor;

    abstract public function toHex(): HexColor;

    abstract public function toHsl(): HslColor;

    abstract public function __toString();

    public function modify(float $saturate = 0.0, float $lighten = 0.0, int $hue = 0): HslColor
    {
        return $this->toHsl()->modify($saturate, $lighten, $hue);
    }

    public function setSaturation(int $s): HslColor
    {
        return $this->toHsl()->setSaturation($s);
    }

    public function setLightness(int $l): HslColor
    {
        return $this->toHsl()->setLightness($l);
    }

    public function setHue(int $h): HslColor
    {
        return $this->toHsl()->setHue($h);
    }

    public static function isValidColor(mixed $color): bool
    {
        return static::isHex($color) || static::isRgb($color) || static::isHsl($color);
    }

    public static function isHex(mixed $color): bool
    {
        return \is_string($color)
            && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color);
    }

    public static function isRgb(mixed $color): bool
    {
        $isRgbFormat = \is_string($color)
            && preg_match('/rgb\(\d{1,3},\s?\d{1,3},\s?\d{1,3}\)/', $color);

        if (! $isRgbFormat) {
            return false;
        }

        $values = static::extractRgbValues($color);

        return self::isRgbValue($values[0])
            && self::isRgbValue($values[1])
            && self::isRgbValue($values[2]);
    }

    public static function isHsl(mixed $color): bool
    {
        $isHslFormat = \is_string($color)
            && preg_match('/hsl\(\d{1,3},\s?\d{1,3}%,\s?\d{1,3}%\)/', $color);

        if (! $isHslFormat) {
            return false;
        }

        [$hue, $saturation, $lightness] = static::extractHslValues($color);

        return $hue >= 0 && $hue <= 360
            && $saturation >= 0 && $saturation <= 100
            && $lightness >= 0 && $lightness <= 100;
    }

    protected static function isRgbValue(int $value): bool
    {
        return $value >= 0 && $value <= 255;
    }

    /**
     * @return int[]
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected static function extractRgbValues(string $color): array
    {
        $values = preg_split('/,\s?/', mb_substr($color, 4, -1));

        if ($values) {
            return array_map('intval', $values);
        }
        throw new ShouldNotHappenException;
    }

    /**
     * @return int[]
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected static function extractHslValues(string $color): array
    {
        $values = preg_split('/,\s?/', mb_substr($color, 4, -1));
        if ($values) {
            $hue = (int) $values[0];
            $saturation = (int) mb_substr($values[1], 0, -1);
            $lightness = (int) mb_substr($values[2], 0, -1);

            return [$hue, $saturation, $lightness];
        }
        throw new ShouldNotHappenException;
    }
}
