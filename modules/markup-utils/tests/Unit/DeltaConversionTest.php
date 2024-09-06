<?php

declare(strict_types=1);

namespace Tests\MarkupUtils\Unit;

use MarkupUtils\Delta;
use MarkupUtils\Plaintext;
use PHPUnit\Framework\TestCase;

class DeltaConversionTest extends TestCase
{
    protected array $delta = [
        ['insert' => 'header 1'],
        ['insert' => "\n", 'attributes' => ['header' => 1]],
        ['insert' => 'header 2'],
        ['insert' => "\n", 'attributes' => ['header' => 2]],
        ['insert' => 'header 3'],
        ['insert' => "\n", 'attributes' => ['header' => 3]],
        ['insert' => 'header 4'],
        ['insert' => "\n", 'attributes' => ['header' => 4]],
        ['insert' => 'bullet 1'],
        ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
        ['insert' => 'bullet 2'],
        ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
        ['insert' => 'list 1'],
        ['insert' => "\n", 'attributes' => ['list' => 'ordered']],
        ['insert' => 'list 2'],
        ['insert' => "\n", 'attributes' => ['list' => 'ordered']],
        ['insert' => 'The first paragraph'],
        ['insert' => "\n"],
        ['attributes' => ['italic' => true], 'insert' => 'italics'],
        ['insert' => ' normal '],
        ['attributes' => ['bold' => true], 'insert' => 'bold'],
        ['insert' => ' '],
        ['attributes' => ['italic' => true, 'bold' => true], 'insert' => 'combined'],
        ['insert' => "\n"],
        ['insert' => 'code'],
        ['insert' => "\n", 'attributes' => ['code-block' => true]],
        ['attributes' => ['link' => 'https://address'], 'insert' => 'link'],
        ['insert' => "\n"],
        ['insert' => ['image' => 'https://address'], 'attributes' => ['attributes' => null]],
        ['insert' => "\n"],
        ['insert' => 'block quote'],
        ['insert' => "\n", 'attributes' => ['blockquote' => true]],
        ['insert' => "\n"],
    ];

    /**
     * Converts delta to html
     *
     * @test
     */
    public function converts_delta_to_html(): void
    {
        static::assertSame(
            <<<'HTML'
            <h1>header 1</h1>
            <h2>header 2</h2>
            <h3>header 3</h3>
            <h4>header 4</h4>
            <ul>
            <li>bullet 1</li>
            <li>bullet 2</li>
            </ul>
            <ol>
            <li>list 1</li>
            <li>list 2</li>
            </ol>
            <p>The first paragraph</p>
            <p><br></p>
            <p><em>italics</em> normal <strong>bold</strong> <em><strong>combined</strong></em></p>
            <pre><code>code</code></pre>
            <p><a href="https://address" target="_blank">link</a></p>
            <p><img src="https://address" alt="" class="img-responsive img-fluid" /></p>
            <blockquote>block quote</blockquote>
            <p><br></p>

            HTML,
            (string) (new Delta($this->delta))->convertToHTML(),
        );
    }

    /**
     * Converts delta to markdown
     *
     * @test
     */
    public function converts_delta_to_markdown(): void
    {
        static::assertSame(
            <<<'MARKDOWN'
            # header 1

            ## header 2

            ### header 3

            #### header 4

            - bullet 1
            - bullet 2

            1. list 1
            2. list 2

            The first paragraph

            *italics* normal **bold** ***combined***

            ```
            code
            ```

            [link](https://address)

            ![](https://address)

            > block quote
            MARKDOWN,
            (string) (new Delta($this->delta))->convertToMarkdown(),
        );
    }

    /**
     * Converts delta to tiptap
     *
     * @test
     */
    public function converts_delta_to_tiptap(): void
    {
        static::assertSame(
            [
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
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'bullet 1']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'bullet 2']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'list 1']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'list 2']],
                        ]],
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
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'hardBreak']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'italics', 'marks' => [['type' => 'italic']]],
                                ['type' => 'text', 'text' => ' normal '],
                                ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'combined', 'marks' => [['type' => 'italic'], ['type' => 'bold']]],
                            ],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'codeBlock',
                            'content' => [['type' => 'text', 'text' => 'code', 'marks' => [['type' => 'code']]]],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [[
                                'type' => 'text',
                                'text' => 'link',
                                'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://address', 'target' => '_blank']]],
                            ]],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [[
                                'type' => 'image',
                                'attrs' => ['src' => 'https://address'],
                            ]],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'blockquote',
                            'content' => [[
                                'type' => 'text', 'text' => 'block quote',
                            ]],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [['type' => 'paragraph', 'content' => [['type' => 'hardBreak']]]],
                    ],
                ],
            ],
            (new Delta($this->delta))->convertToTipTap()->toArray()
        );
    }

    /**
     * Converts delta to plaintext
     *
     * @test
     */
    public function converts_delta_to_plaintext(): void
    {
        static::assertSame(
            <<<'PLAINTEXT'
            header 1
            header 2
            header 3
            header 4
            bullet 1
            bullet 2
            list 1
            list 2
            The first paragraph
            italics normal bold combined
            code
            link

            block quote


            PLAINTEXT,
            (string) (new Delta($this->delta))->convertToPlaintext(),
        );
    }
}
