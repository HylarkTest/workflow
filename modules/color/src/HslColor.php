<?php

declare(strict_types=1);

namespace Color;

class HslColor extends Color
{
    protected float $h;

    protected float $s;

    protected float $l;

    public function __construct(int $h, int $s, int $l)
    {
        $this->h = (float) $h / 360;
        $this->s = (float) $s / 100;
        $this->l = (float) $l / 100;
    }

    public function toRgb(): RgbColor
    {
        $h = $this->h;
        $s = $this->s;
        $l = $this->l;

        if ($s === 0.0) {
            return new RgbColor((int) round($l * 255), (int) round($l * 255), (int) round($l * 255));
        }

        if ($l < 0.5) {
            $var2 = $l * (1 + $s);
        } else {
            $var2 = ($l + $s) - ($s * $l);
        }

        $var1 = 2 * $l - $var2;
        $r = $this->hue2Rgb($var1, $var2, $h + (1 / 3));
        $g = $this->hue2Rgb($var1, $var2, $h);
        $b = $this->hue2Rgb($var1, $var2, $h - (1 / 3));

        return new RgbColor((int) round($r * 255), (int) round($g * 255), (int) round($b * 255));
    }

    public function toHex(): HexColor
    {
        return $this->toRgb()->toHex();
    }

    public function toHsl(): self
    {
        return $this;
    }

    public function __toString()
    {
        $h = (int) round($this->h * 360);
        $s = (int) round($this->s * 100);
        $l = (int) round($this->l * 100);

        return "hsl({$h}, {$s}%, {$l}%)";
    }

    public function modify(float $saturate = 0.0, float $lighten = 0.0, int $hue = 0): self
    {
        $saturation = $this->s;
        if ($saturate < 0) {
            $saturation += $saturate * $saturation;
        } elseif ($saturate > 0) {
            $saturation += $saturate * (1 - $saturation);
        }

        $lightness = $this->l;
        if ($lighten < 0) {
            $lightness += $lighten * $lightness;
        } elseif ($lighten > 0) {
            $lightness += $lighten * (1 - $lightness);
        }

        $newHue = fmod($this->h + ($hue / 360), 1);
        if ($newHue < 0) {
            $newHue = 1 - $newHue;
        }

        return new self((int) round($newHue * 360), (int) round($saturation * 100), (int) round($lightness * 100));
    }

    public function setSaturation(int $s): self
    {
        $this->s = $s / 100;

        return $this;
    }

    public function setLightness(int $l): self
    {
        $this->l = $l / 100;

        return $this;
    }

    public function setHue(int $h): self
    {
        $this->h = $h / 360;

        return $this;
    }

    protected function hue2Rgb(float $v1, float $v2, float $vh): float
    {
        if ($vh < 0) {
            $vh++;
        }

        if ($vh > 1) {
            $vh--;
        }

        if ((6 * $vh) < 1) {
            return $v1 + ($v2 - $v1) * 6 * $vh;
        }

        if ((2 * $vh) < 1) {
            return $v2;
        }

        if ((3 * $vh) < 2) {
            return $v1 + ($v2 - $v1) * ((2 / 3 - $vh) * 6);
        }

        return $v1;
    }
}
