<?php

declare(strict_types=1);

namespace Color;

class HexColor extends Color
{
    protected string $hexCode;

    public function __construct(string $hexCode)
    {
        $this->hexCode = mb_strtolower(trim($hexCode, '#'));
    }

    public function toRgb(): RgbColor
    {
        $hex = $this->hexCode;
        $isShorthand = mb_strlen($hex) === 3;

        $redHex = $isShorthand ? $hex[0].$hex[0] : mb_substr($hex, 0, 2);
        $greenHex = $isShorthand ? $hex[1].$hex[1] : mb_substr($hex, 2, 2);
        $blueHex = $isShorthand ? $hex[2].$hex[2] : mb_substr($hex, 4, 2);

        return new RgbColor(
            (int) hexdec($redHex),
            (int) hexdec($greenHex),
            (int) hexdec($blueHex),
        );
    }

    public function toHex(): self
    {
        return $this;
    }

    public function toHsl(): HslColor
    {
        return $this->toRgb()->toHsl();
    }

    public function __toString()
    {
        return "#$this->hexCode";
    }
}
