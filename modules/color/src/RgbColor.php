<?php

declare(strict_types=1);

namespace Color;

use PHPStan\ShouldNotHappenException;

class RgbColor extends Color
{
    protected float $r;

    protected float $g;

    protected float $b;

    public function __construct(int $r, int $g, int $b)
    {
        $this->r = (float) $r / 255;
        $this->g = (float) $g / 255;
        $this->b = (float) $b / 255;
    }

    public function toRgb(): self
    {
        return $this;
    }

    public function toHex(): HexColor
    {
        return new HexColor(
            str_pad(dechex((int) round($this->r * 255)), 2, '0', \STR_PAD_LEFT).
            str_pad(dechex((int) round($this->g * 255)), 2, '0', \STR_PAD_LEFT).
            str_pad(dechex((int) round($this->b * 255)), 2, '0', \STR_PAD_LEFT)
        );
    }

    public function toHsl(): HslColor
    {
        $rgbArray = [$this->r, $this->g, $this->b];

        $minValue = min($rgbArray);
        $maxValue = max($rgbArray);
        $maxDelta = $maxValue - $minValue;

        $lightness = ($maxValue + $minValue) / 2;

        if ($maxDelta === 0.0) {
            $hue = 0.0;
            $saturation = 0.0;
        } else {
            if ($lightness < 0.5) {
                $saturation = $maxDelta / ($maxValue + $minValue);
            } else {
                $saturation = $maxDelta / (2 - $maxValue - $minValue);
            }

            $deltaRed = ((($maxValue - $this->r) / 6) + ($maxDelta / 2)) / $maxDelta;
            $deltaGreen = ((($maxValue - $this->g) / 6) + ($maxDelta / 2)) / $maxDelta;
            $deltaBlue = ((($maxValue - $this->b) / 6) + ($maxDelta / 2)) / $maxDelta;

            if ($this->r === $maxValue) {
                $hue = $deltaBlue - $deltaGreen;
            } elseif ($this->g === $maxValue) {
                $hue = (1 / 3) + $deltaRed - $deltaBlue;
            } elseif ($this->b === $maxValue) {
                $hue = (2 / 3) + $deltaGreen - $deltaRed;
            } else {
                throw new ShouldNotHappenException('All cases are specified in the if statements');
            }

            if ($hue < 0) {
                $hue++;
            }

            if ($hue > 1) {
                $hue--;
            }
        }

        return new HslColor((int) round($hue * 360), (int) round($saturation * 100), (int) round($lightness * 100));
    }

    public function __toString()
    {
        $r = (int) round($this->r * 255);
        $g = (int) round($this->g * 255);
        $b = (int) round($this->b * 255);

        return "rgb($r, $g, $b)";
    }
}
