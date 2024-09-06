<?php

declare(strict_types=1);

namespace MarkupUtils\Converters;

use MarkupUtils\Delta;
use MarkupUtils\Markdown;
use Illuminate\Support\Str;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;

/**
 * @phpstan-import-type DeltaEmbed from \MarkupUtils\Delta
 * @phpstan-import-type DeltaInsert from \MarkupUtils\Delta
 * @phpstan-import-type DeltaOp from \MarkupUtils\Delta
 * @phpstan-import-type DeltaOps from \MarkupUtils\Delta
 * @phpstan-import-type DeltaAttributes from \MarkupUtils\Delta
 *
 * @phpstan-type DeltaOpInProgress array{
 *     insert?: DeltaInsert,
 *     attributes?: DeltaAttributes,
 * }
 */
class MarkdownToDelta
{
    public static function convert(Markdown $markdown): Delta
    {
        return static::convertChildren(null, $markdown->getTree());
    }

    /**
     * @param  DeltaOpInProgress  $op
     * @param  array<string, mixed>  $extra
     */
    protected static function convertChildren(?Node $parent, Node $node, array $op = [], int $indent = 0, array $extra = []): Delta
    {
        $children = $node->children();

        $delta = new Delta([]);

        $previous = null;

        foreach ($children as $child) {
            if ($child instanceof AbstractBlock && $previous instanceof AbstractBlock) {
                $delta->insert("\n");
            }
            switch (true) {
                case $child instanceof Paragraph:
                    $delta->concat(static::convertChildren($node, $child, $op, $indent + 1));
                    if (! $parent) {
                        $delta->insert("\n");
                    }
                    break;
                case $child instanceof Code:
                    $lines = explode("\n", $child->getLiteral());
                    foreach ($lines as $line) {
                        if ($line) {
                            $delta->insert($line);
                        }
                        $delta->insert("\n", ['code-block' => true]);
                    }
                    break;
                case $child instanceof Table:
                case $child instanceof ListBlock:
                    $delta->concat(static::convertChildren($node, $child, $op, $indent));
                    break;
                case $child instanceof TaskListItemMarker:
                case $child instanceof ListItem:
                    $delta->concat(static::convertListItem($node, $child, $indent));
                    break;
                case $child instanceof TableRow:
                    $delta->concat(static::convertChildren($node, $child, $op, $indent, [
                        'id' => 'row-'.Str::random(4),
                    ]));
                    break;
                case $child instanceof TableCell:
                    $align = $child->getAlign();
                    $delta->concat(static::convertTableCell($node, $child, $extra['id'] ?? null, $align));
                    break;
                case $child instanceof Heading:
                    $delta->concat(static::convertChildren($node, $child, $op, $indent + 1));
                    $delta->insert("\n", ['header' => $child->getLevel() ?: 1]);
                    break;
                case $child instanceof BlockQuote:
                    $delta->concat(static::convertChildren($node, $child, $op, $indent + 1));
                    $delta->insert("\n", ['blockquote' => true]);
                    break;
                case $child instanceof ThematicBreak:
                    $delta->insert(['divider' => true]);
                    $delta->insert("\n");
                    break;
                case $child instanceof Image:
                    /** @var DeltaOp $op */
                    $delta->concat(static::embedFormat($op, ['image' => $child->getUrl()], ['attributes' => $child->getTitle()]));
                    break;
                default:
                    $delta->concat(static::convertInline($node, $child, $op));
                    break;
            }

            $previous = $child;
        }

        return $delta;
    }

    protected static function convertListItem(Node $parent, ListItem|TaskListItemMarker $node, int $indent): Delta
    {
        $delta = new Delta([]);

        foreach ($node->children() as $child) {
            $delta->concat(static::convertChildren($parent, $node, [], $indent + 1));
            if (! ($child instanceof ListBlock)) {
                $listAttribute = match (true) {
                    $parent instanceof ListBlock && $parent->getListData()->type === ListBlock::TYPE_ORDERED => 'ordered',
                    $node instanceof TaskListItemMarker => $node->isChecked() ? 'checked' : 'unchecked',
                    default => 'bullet',
                };

                $attributes = ['list' => $listAttribute];

                if ($indent) {
                    $attributes['indent'] = $indent;
                }

                $delta->insert("\n", $attributes);
            }
        }

        return $delta;
    }

    protected static function convertTableCell(Node $parent, Node $node, ?string $id, ?string $align): Delta
    {
        $delta = static::convertChildren($parent, $node, [], 1);
        $attributes = ['table' => $id];
        if ($align && $align !== 'left') {
            $attributes['align'] = $align;
        }
        $delta->insert("\n", $attributes);

        return $delta;
    }

    /**
     * @param  DeltaOp  $op
     * @param  DeltaEmbed  $embed
     * @param  DeltaAttributes  $attributes
     */
    protected static function embedFormat(array $op, array $embed, array $attributes = []): Delta
    {
        return (new Delta)->insert($embed, array_merge($op['attributes'] ?? [], $attributes));
    }

    /**
     * @param  DeltaOpInProgress  $op
     */
    protected static function convertInline(Node $parent, Node $child, array $op): Delta
    {
        return match (true) {
            $child instanceof Strong => static::inlineFormat($parent, $child, $op, ['bold' => true]),
            $child instanceof Emphasis => static::inlineFormat($parent, $child, $op, ['italic' => true]),
            $child instanceof Strikethrough => static::inlineFormat($parent, $child, $op, ['strike' => true]),
            $child instanceof Code => static::inlineFormat($parent, $child, $op, ['code' => true]),
            $child instanceof Link => static::inlineFormat($parent, $child, $op, ['link' => $child->getUrl()]),
            default => static::inlineFormat($parent, $child, $op, []),
        };
    }

    /**
     * @param  DeltaOpInProgress  $op
     * @param  DeltaAttributes  $attributes
     */
    protected static function inlineFormat(Node $parent, Node $node, array $op, array $attributes = []): Delta
    {
        $text = $node instanceof AbstractStringContainer ? $node->getLiteral() : null;

        $attributes = array_merge($op['attributes'] ?? [], $attributes);

        if ($text) {
            $op['insert'] = $text;
        }

        if (! empty($attributes)) {
            $op['attributes'] = $attributes;
        }

        if ($node->hasChildren()) {
            return static::convertChildren($parent, $node, $op);
        }

        if ($op['insert'] ?? false) {
            return new Delta([$op]);
        }

        return new Delta;
    }
}
