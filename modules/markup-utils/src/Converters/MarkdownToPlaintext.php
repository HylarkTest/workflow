<?php

declare(strict_types=1);

namespace MarkupUtils\Converters;

use MarkupUtils\Markdown;
use MarkupUtils\Plaintext;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;

class MarkdownToPlaintext
{
    public static function convert(Markdown $markdown): Plaintext
    {
        return new Plaintext(static::convertChildren(null, $markdown->getTree()));
    }

    protected static function convertChildren(?Node $parent, Node $node): string
    {
        $children = $node->children();

        $plaintext = '';

        $previous = null;

        foreach ($children as $child) {
            if ($child instanceof AbstractBlock && $previous instanceof AbstractBlock) {
                $plaintext .= "\n";
            }
            switch (true) {
                case $child instanceof Paragraph:
                    $plaintext .= static::convertChildren($node, $child);
                    if (! $parent) {
                        $plaintext .= "\n";
                    }
                    break;
                case $child instanceof FencedCode:
                case $child instanceof Code:
                    $lines = explode("\n", $child->getLiteral());
                    foreach ($lines as $line) {
                        if ($line) {
                            $plaintext .= $line;
                        }
                        $plaintext .= "\n";
                    }
                    break;
                case $child instanceof Table:
                case $child instanceof ListBlock:
                case $child instanceof TableRow:
                    $plaintext .= static::convertChildren($node, $child);
                    break;
                case $child instanceof TaskListItemMarker:
                case $child instanceof ListItem:
                    $plaintext .= static::convertListItem($node, $child);
                    break;
                case $child instanceof BlockQuote:
                case $child instanceof Heading:
                case $child instanceof TableCell:
                case $child instanceof Image:
                case $child instanceof Link:
                    $plaintext .= static::convertChildren($node, $child)."\n";
                    break;
                case $child instanceof ThematicBreak:
                    break;
                default:
                    $plaintext .= static::inlineFormat($node, $child);
                    break;
            }

            $previous = $child;
        }

        return $plaintext;
    }

    protected static function convertListItem(Node $parent, ListItem|TaskListItemMarker $node): string
    {
        $plaintext = '';

        foreach ($node->children() as $child) {
            $plaintext = static::convertChildren($parent, $node);
            if (! ($child instanceof ListBlock)) {
                $plaintext .= "\n";
            }
        }

        return $plaintext;
    }

    protected static function inlineFormat(Node $parent, Node $node): string
    {
        $plaintext = $node instanceof AbstractStringContainer ? $node->getLiteral() : '';

        if ($node->hasChildren()) {
            $plaintext .= static::convertChildren($parent, $node);
        }

        return $plaintext;
    }
}
