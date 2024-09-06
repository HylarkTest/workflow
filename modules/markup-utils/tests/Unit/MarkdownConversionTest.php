<?php

declare(strict_types=1);

namespace Tests\MarkupUtils\Unit;

use MarkupUtils\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownConversionTest extends TestCase
{
    protected string $markdown = <<<'MARKDOWN'
        # header 1
        ## header 2
        ### header 3
        #### header 4
        alt-h1
        ======
        alt-h2
        ------
        * bullet 1
        * bullet 2
        + other bullet 1
        + other bullet 2
        - other other bullet 1
        - other other bullet 2
        1. list 1
        2. list 2

        The first paragraph

        *italics* _alt-italics_ normal **bold** __alt-bold__ **_combined_** ***alt-combined*** `code`

        ```
        code block
        ```
        [link](https://address)

        ![image](https://address)
        > block quote
        ---
        ***
        ___
        MARKDOWN;

    /**
     * Converts markdown to delta
     *
     * @test
     */
    public function converts_markdown_to_delta(): void
    {
        static::assertSame(
            ['ops' => [
                ['insert' => 'header 1'],
                ['insert' => "\n", 'attributes' => ['header' => 1]],
                ['insert' => "\n"],
                ['insert' => 'header 2'],
                ['insert' => "\n", 'attributes' => ['header' => 2]],
                ['insert' => "\n"],
                ['insert' => 'header 3'],
                ['insert' => "\n", 'attributes' => ['header' => 3]],
                ['insert' => "\n"],
                ['insert' => 'header 4'],
                ['insert' => "\n", 'attributes' => ['header' => 4]],
                ['insert' => "\n"],
                ['insert' => 'alt-h1'],
                ['insert' => "\n", 'attributes' => ['header' => 1]],
                ['insert' => "\n"],
                ['insert' => 'alt-h2'],
                ['insert' => "\n", 'attributes' => ['header' => 2]],
                ['insert' => "\n"],
                ['insert' => 'bullet 1'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'bullet 2'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'other bullet 1'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'other bullet 2'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'other other bullet 1'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'other other bullet 2'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'list 1'],
                ['insert' => "\n", 'attributes' => ['list' => 'ordered']],
                ['insert' => "\n"],
                ['insert' => 'list 2'],
                ['insert' => "\n", 'attributes' => ['list' => 'ordered']],
                ['insert' => "\n"],
                ['insert' => 'The first paragraph'],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['attributes' => ['italic' => true], 'insert' => 'italics'],
                ['insert' => ' '],
                ['attributes' => ['italic' => true], 'insert' => 'alt-italics'],
                ['insert' => ' normal '],
                ['attributes' => ['bold' => true], 'insert' => 'bold'],
                ['insert' => ' '],
                ['attributes' => ['bold' => true], 'insert' => 'alt-bold'],
                ['insert' => ' '],
                ['attributes' => ['bold' => true, 'italic' => true], 'insert' => 'combined'],
                ['insert' => ' '],
                ['attributes' => ['italic' => true, 'bold' => true], 'insert' => 'alt-combined'],
                ['insert' => ' '],
                ['insert' => 'code'],
                ['insert' => "\n", 'attributes' => ['code-block' => true]],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['attributes' => ['link' => 'https://address'], 'insert' => 'link'],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => ['image' => 'https://address'], 'attributes' => ['attributes' => null]],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => 'block quote'],
                ['insert' => "\n", 'attributes' => ['blockquote' => true]],
                ['insert' => "\n"],
                ['insert' => ['divider' => true]],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => ['divider' => true]],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => ['divider' => true]],
                ['insert' => "\n"],
            ]],
            (new Markdown($this->markdown))->convertToDelta()->toArray()
        );
    }

    /**
     * Converts markdown to tiptap
     *
     * @test
     */
    public function converts_markdown_to_tiptap(): void
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
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'heading',
                            'attrs' => ['level' => 1],
                            'content' => [['type' => 'text', 'text' => 'alt-h1']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'heading',
                            'attrs' => ['level' => 2],
                            'content' => [['type' => 'text', 'text' => 'alt-h2']],
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
                            'content' => [['type' => 'text', 'text' => 'other bullet 1']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'other bullet 2']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'other other bullet 1']],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'other other bullet 2']],
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
                            'content' => [
                                ['type' => 'text', 'text' => 'italics', 'marks' => [['type' => 'italic']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'alt-italics', 'marks' => [['type' => 'italic']]],
                                ['type' => 'text', 'text' => ' normal '],
                                ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'alt-bold', 'marks' => [['type' => 'bold']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'combined', 'marks' => [['type' => 'bold'], ['type' => 'italic']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'alt-combined', 'marks' => [['type' => 'italic'], ['type' => 'bold']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'code', 'marks' => [['type' => 'code']]],
                            ],
                        ]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'codeBlock',
                            'content' => [['type' => 'text', 'text' => 'code block
', 'marks' => [['type' => 'code']]]],
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
                                'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://address']]],
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
                                'attrs' => ['src' => 'https://address', 'alt' => 'image'],
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
                        'content' => [['type' => 'horizontalRule', 'content' => []]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [['type' => 'horizontalRule', 'content' => []]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [['type' => 'horizontalRule', 'content' => []]],
                    ],
                ],
            ],
            (new Markdown($this->markdown))->convertToTipTap()->toArray()
        );
    }

    /**
     * Converts markdown to html
     *
     * @test
     */
    public function converts_markdown_to_html(): void
    {
        static::assertSame(
            <<<'HTML'
            <h1>header 1</h1>
            <h2>header 2</h2>
            <h3>header 3</h3>
            <h4>header 4</h4>
            <h1>alt-h1</h1>
            <h2>alt-h2</h2>
            <ul>
            <li>bullet 1</li>
            <li>bullet 2</li>
            </ul>
            <ul>
            <li>other bullet 1</li>
            <li>other bullet 2</li>
            </ul>
            <ul>
            <li>other other bullet 1</li>
            <li>other other bullet 2</li>
            </ul>
            <ol>
            <li>list 1</li>
            <li>list 2</li>
            </ol>
            <p>The first paragraph</p>
            <p><em>italics</em> <em>alt-italics</em> normal <strong>bold</strong> <strong>alt-bold</strong> <strong><em>combined</em></strong> <em><strong>alt-combined</strong></em> <code>code</code></p>
            <pre><code>code block
            </code></pre>
            <p><a href="https://address">link</a></p>
            <p><img src="https://address" alt="image" /></p>
            <blockquote>
            <p>block quote</p>
            </blockquote>
            <hr />
            <hr />
            <hr />

            HTML,
            (string) (new Markdown($this->markdown))->convertToHTML()
        );
    }

    /**
     * Converts markdown to plaintext
     *
     * @test
     */
    public function converts_markdown_to_plaintext(): void
    {
        static::assertSame(
            <<<'PLAINTEXT'
            header 1

            header 2

            header 3

            header 4

            alt-h1

            alt-h2

            bullet 1

            bullet 2

            other bullet 1

            other bullet 2

            other other bullet 1

            other other bullet 2

            list 1

            list 2

            The first paragraph

            italics alt-italics normal bold alt-bold combined alt-combined code


            code block


            link


            image


            block quote




            PLAINTEXT,
            (string) (new Markdown($this->markdown))->convertToPlaintext()
        );
    }
}
