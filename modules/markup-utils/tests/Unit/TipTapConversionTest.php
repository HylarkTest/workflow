<?php

declare(strict_types=1);

namespace Tests\MarkupUtils\Unit;

use MarkupUtils\Delta;
use MarkupUtils\TipTap;
use MarkupUtils\Plaintext;
use PHPUnit\Framework\TestCase;

class TipTapConversionTest extends TestCase
{
    protected array $tiptap = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [['type' => 'text', 'text' => 'header 1']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
                    'content' => [['type' => 'text', 'text' => 'header 2']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'heading',
                    'attrs' => ['level' => 3],
                    'content' => [['type' => 'text', 'text' => 'header 3']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'heading',
                    'attrs' => ['level' => 4],
                    'content' => [['type' => 'text', 'text' => 'header 4']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                'content' => [['type' => 'text', 'text' => 'bullet 1']],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                'content' => [['type' => 'text', 'text' => 'bullet 2']],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 1, 'hasBullet' => true],
                'content' => [['type' => 'text', 'text' => 'sub bullet']],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'The first paragraph']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'right', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'aligned right']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 2, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'indented']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'right', 'indent' => 2, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'right indented']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [['type' => 'paragraph']],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'italics'],
                        ['type' => 'text', 'text' => ' normal '],
                        ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                        ['type' => 'text', 'text' => ' '],
                        ['type' => 'text', 'marks' => [['type' => 'bold'], ['type' => 'italic']], 'text' => 'combined'],
                    ],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'codeBlock',
                    'attrs' => ['language' => null],
                    'content' => [['type' => 'text', 'text' => 'code']],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://address']]],
                        'text' => 'link',
                    ]],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [[
                    'type' => 'blockquote',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'block quote']],
                    ]],
                ]],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [['type' => 'paragraph']],
            ],
        ],
    ];

    /**
     * Converts delta to html
     *
     * @test
     */
    public function converts_tiptap_to_html(): void
    {
        static::assertSame(
            '<h1>header 1</h1><h2>header 2</h2><h3>header 3</h3><h4>header 4</h4><ul><li>bullet 1</li><li>bullet 2<ul><li>sub bullet</li></ul></li></ul><p>The first paragraph</p><div style="text-align: right;"><p>aligned right</p></div><div style="padding-left: 6em;"><p>indented</p></div><div style="text-align: right; padding-right: 6em;"><p>right indented</p></div><p></p><p><em>italics</em> normal <strong>bold</strong> <strong><em>combined</em></strong></p><pre><code class="hljs ">code</code></pre><p><a target="_blank" rel="noopener noreferrer nofollow" href="https://address">link</a></p><blockquote><p>block quote</p></blockquote><p></p>',
            (string) (new TipTap($this->tiptap))->convertToHTML(),
        );
    }

    /**
     * Converts delta to markdown
     *
     * @test
     */
    public function converts_tiptap_to_markdown(): void
    {
        static::assertSame(
            <<<'MARKDOWN'
            # header 1

            ## header 2

            ### header 3

            #### header 4

            - bullet 1
            - bullet 2
                - sub bullet

            The first paragraph

            aligned right

            indented

            right indented

            *italics* normal **bold** ***combined***

            ```
            code
            ```

            [link](https://address)

            > block quote
            MARKDOWN,
            (string) (new TipTap($this->tiptap))->convertToMarkdown(),
        );
    }

    /**
     * Converts delta to plaintext
     *
     * @test
     */
    public function converts_tiptap_to_plaintext(): void
    {
        static::assertSame(
            <<<'PLAINTEXT'
            header 1

            header 2

            header 3

            header 4

            bullet 1

            bullet 2
            sub bullet

            The first paragraph

            aligned right

            indented

            right indented

            italics normal bold combined

            code


            link


            block quote

            PLAINTEXT,
            (string) (new TipTap($this->tiptap))->convertToPlaintext(),
        );
    }
}
