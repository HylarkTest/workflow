<?php

declare(strict_types=1);

namespace Tests\MarkupUtils\Unit;

use MarkupUtils\Plaintext;
use PHPUnit\Framework\TestCase;

class PlaintextConversionTest extends TestCase
{
    /**
     * Converts plaintext to delta
     *
     * @test
     */
    public function converts_plaintext_to_delta(): void
    {
        static::assertSame(['ops' => [[
            'insert' => 'Hello this is plaintext',
        ]]], (new Plaintext('Hello this is plaintext'))->convertToDelta()->toArray());
    }

    /**
     * Converts plaintext to tiptap
     *
     * @test
     */
    public function converts_plaintext_to_tiptap(): void
    {
        static::assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'rootblock',
                    'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Hello this is plaintext']],
                    ]],
                ],
            ],
        ], (new Plaintext('Hello this is plaintext'))->convertToTipTap()->toArray());
    }

    /**
     * Converts plaintext to markdown
     *
     * @test
     */
    public function converts_plaintext_to_markdown(): void
    {
        static::assertSame(
            'Hello this is plaintext',
            (string) (new Plaintext('Hello this is plaintext'))->convertToMarkdown()
        );
    }

    /**
     * Converts plaintext to html
     *
     * @test
     */
    public function converts_plaintext_to_html(): void
    {
        static::assertSame(
            '<p>Hello this is plaintext</p>',
            (string) (new Plaintext('Hello this is plaintext'))->convertToHTML()
        );
    }
}
