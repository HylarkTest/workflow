<?php

declare(strict_types=1);

namespace Tests\MarkupUtils\Unit;

use MarkupUtils\HTML;
use MarkupUtils\Markdown;
use MarkupUtils\Plaintext;
use PHPUnit\Framework\TestCase;

class HTMLConversionTest extends TestCase
{
    protected string $html = <<<'HTML'
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
        <p style="text-align: right;">aligned right</p>
        <p style="padding-left: 6em;">indented</p>
        <p><em>italics</em> normal <strong>bold</strong> <strong><em>combined</em></strong> <code>code</code></p>
        <pre><code>code block
        </code></pre>
        <p><a href="https://address">link</a></p>
        <p><img src="https://address" alt="image" /></p>
        <blockquote>
        <p>block quote</p>
        </blockquote>
        <hr />
        HTML;

    /**
     * Converts html to delta
     *
     * @test
     */
    public function converts_html_to_delta(): void
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
                ['insert' => 'bullet 1'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'bullet 2'],
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
                ['insert' => 'aligned right'],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['insert' => 'indented'],
                ['insert' => "\n"],
                ['insert' => "\n"],
                ['attributes' => ['italic' => true], 'insert' => 'italics'],
                ['insert' => ' normal '],
                ['attributes' => ['bold' => true], 'insert' => 'bold'],
                ['insert' => ' '],
                ['attributes' => ['italic' => true, 'bold' => true], 'insert' => 'combined'],
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
            ]],
            (new HTML($this->html))->convertToDelta()->toArray(),
        );
    }

    /**
     * Converts html to markdown
     *
     * @test
     */
    public function converts_html_to_markdown(): void
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

            aligned right

            indented

            *italics* normal **bold** ***combined*** `code`

            ```
            code block

            ```

            [link](https://address)

            ![image](https://address)

            > block quote

            ---
            MARKDOWN,
            (string) (new HTML($this->html))->convertToMarkdown(),
        );
    }

    /**
     * Converts html to tiptap
     *
     * @test
     */
    public function converts_html_to_tiptap(): void
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
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'italics', 'marks' => [['type' => 'italic']]],
                                ['type' => 'text', 'text' => ' normal '],
                                ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold']]],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'text' => 'combined', 'marks' => [['type' => 'bold'], ['type' => 'italic']]],
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
                ],
            ],
            (new HTML($this->html))->convertToTipTap()->toArray()
        );
    }

    /**
     * Converts html to plaintext
     *
     * @test
     */
    public function converts_html_to_plaintext(): void
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
            aligned right
            indented
            italics normal bold combined code
            code block

            link


            block quote
            PLAINTEXT,
            (string) (new HTML($this->html))->convertToPlaintext()
        );
    }
}
