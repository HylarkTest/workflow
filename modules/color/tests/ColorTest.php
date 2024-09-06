<?php

declare(strict_types=1);

namespace Tests\Color;

use Color\HexColor;

class ColorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Colors can be converted to different formats
     *
     * @test
     */
    public function colors_can_be_converted_to_different_formats(): void
    {
        $hex = new HexColor('#35b8a4');
        $rgb = $hex->toRgb();
        $hsl = $hex->toHsl();

        static::assertSame('rgb(53, 184, 164)', (string) $rgb);
        static::assertSame('hsl(171, 55%, 46%)', (string) $hsl);

        static::assertSame('hsl(171, 55%, 46%)', (string) $rgb->toHsl());
        static::assertSame('#35b8a4', (string) $rgb->toHex());

        // Some rounding errors are expected.
        static::assertSame('rgb(53, 182, 162)', (string) $hsl->toRgb());
        static::assertSame('#35b6a2', (string) $hsl->toHex());
    }
}
