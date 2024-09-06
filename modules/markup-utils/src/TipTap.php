<?php

declare(strict_types=1);

namespace MarkupUtils;

use Tiptap\Editor;
use Tiptap\Marks\Link;
use Tiptap\Nodes\Image;
use Tiptap\Marks\Highlight;
use Tiptap\Marks\Subscript;
use Tiptap\Marks\TextStyle;
use Tiptap\Marks\Underline;
use Tiptap\Marks\Superscript;
use Tiptap\Extensions\StarterKit;
use Tiptap\Nodes\CodeBlockHighlight;
use MarkupUtils\TipTapExtensions\Color;
use Illuminate\Contracts\Support\Arrayable;
use MarkupUtils\TipTapExtensions\Paragraph;
use MarkupUtils\TipTapExtensions\Rootblock;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class TipTap extends Markup implements Arrayable
{
    protected ?Editor $editor = null;

    public static function getEditor(): Editor
    {
        return new Editor([
            'extensions' => [
                new Rootblock,
                new StarterKit([
                    'paragraph' => false,
                    'blockquote' => [],
                    'heading' => [],
                    'codeBlock' => false,
                    'italic' => [],
                    'bulletList' => false,
                    'orderedList' => false,
                    'listItem' => false,
                ]),
                new Paragraph([]),
                new CodeBlockHighlight([]),
                new Underline,
                new TextStyle,
                new Color,
                new Superscript,
                new Subscript,
                new Highlight(['multicolor' => true, []]),
                new Link([]),
                new Image([]),
            ],
        ]);
    }

    public function __construct(public readonly array $tiptap) {}

    protected function editor(): Editor
    {
        if ($this->editor === null) {
            $this->editor = static::getEditor();
            $this->editor->setContent($this->tiptap);
        }

        return $this->editor;
    }

    public function convertToPlaintext(): Plaintext
    {
        return $this->convertToMarkdown()->convertToPlaintext();
    }

    public function convertToHTML(): HTML
    {
        $html = $this->editor()->getHTML();

        return new HTML($html);
    }

    public function convertToMarkdown(): Markdown
    {
        $content = $this->tiptap;
        foreach ($content['content'] as $index => $rootblock) {
            // Alignment and indent are not supported in markdown, except when
            // indent is for nested bullet lists.
            $content['content'][$index]['attrs']['alignment'] = 'left';
            if (! ($rootblock['attrs']['hasBullet'] ?? null)) {
                $content['content'][$index]['attrs']['indent'] = 0;
            }
        }
        $editor = static::getEditor();
        $editor->setContent($content);
        $html = $editor->getHTML();

        return (new HTML($html))->convertToMarkdown();
    }

    public function convertToDelta(): Delta
    {
        return $this->convertToMarkdown()->convertToDelta();
    }

    public function convertToTipTap(): self
    {
        return $this;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), \JSON_THROW_ON_ERROR);
    }

    public function toArray()
    {
        return $this->tiptap;
    }
}
