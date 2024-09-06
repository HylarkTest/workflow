<?php

declare(strict_types=1);

namespace MarkupUtils\TipTapExtensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;
use MarkupUtils\TipTap;
use Tiptap\Core\DOMParser;
use Tiptap\Core\DOMSerializer;
use PHPStan\ShouldNotHappenException;

/**
 * The rootblock component is a custom extension for Hylark that will allow
 * reordering lines and list items similar to the ClickUp editor.
 * It is a wrapper block that contains attributes for the child tags, including
 * whether the child is a list item, which makes things tricky in regard to
 * rendering HTML. This is because the TipTap editor pretty much assumes that
 * each node block corresponds to a single HTML tag. But for lists that is not
 * the case, because a list needs to be wrapped in an ul/ol tag, so the first
 * rootblock with `hasBullet` set to true will open an ul tag, and the last
 * rootblock with `hasBullet` set to true will close the ul tag.
 * This is the main reason for the complexity in this class.
 */
class Rootblock extends Node
{
    /**
     * @var string
     */
    public static $name = 'rootblock';

    /**
     * Rootblocks must always be rendered first as they are the parent node of
     * everything else.
     *
     * @var int
     */
    public static $priority = 9999;

    protected static bool $wrapping = false;

    protected static ?\DOMNode $parsingNode = null;

    protected static int $listLevel = 0;

    protected static bool $openingDiv = false;

    /**
     * @return array<string, mixed>
     */
    public function addOptions()
    {
        return [
            'alignment' => ['left', 'center', 'right'],
            'indent' => [0, 1, 2, 3, 4, 5, 6],
            'hasBullet' => [false, true],
            'HTMLAttributes' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function addAttributes()
    {
        return [
            'alignment' => [
                'default' => 'left',
                'parseHTML' => function ($DOMNode) {
                    $style = $DOMNode->getAttribute('style');
                    preg_match('/text-align: (left|center|right)/', $style, $matches);

                    return $matches[1] ?? 'left';
                },
                'renderHTML' => function ($attributes) {
                    if ($attributes->alignment === 'left') {
                        return [];
                    }

                    return [
                        'style' => 'text-align: '.$attributes->alignment,
                    ];
                },
            ],
            'indent' => [
                'default' => 0,
                'parseHTML' => function ($DOMNode) {
                    $style = $DOMNode->getAttribute('style');
                    preg_match('/text-align: (left|center|right)/', $style, $matches);
                    $align = ($matches[1] ?? 'left') === 'right' ? 'right' : 'left';
                    preg_match("/padding-$align: (\d+)em/", $style, $matches);

                    return (int) ($matches[1] ?? 0) / 3;
                },
                'renderHTML' => function ($attributes) {
                    if ($attributes->indent === 0 || $attributes->hasBullet === true) {
                        return [];
                    }
                    $align = ($attributes->alignment ?? 'left') === 'right' ? 'right' : 'left';

                    return [
                        'style' => "padding-$align: ".($attributes->indent * 3).'em',
                    ];
                },
            ],
            'hasBullet' => [
                'parseHTML' => function ($DOMNode) {
                    $parent = $DOMNode->parentNode;
                    $parentTag = $parent instanceof \DOMElement ? $parent->tagName : null;

                    return $parentTag === 'ul' || $parentTag === 'ol';
                },
                'default' => false,
            ],
        ];
    }

    /**
     * This function should return an array of arrays that tell the parser if a
     * tag matches this particular node. For rootblocks every top level tag is
     * a match, so we return an array with the tag name to guarantee that it will
     * match with this node if the parent is a body, ul, or ol tag.
     *
     * @param  ?\DOMElement  $node
     * @return array<array{tag: string, attrs?: array<string, mixed>}>
     */
    public function parseHTML($node = null): ?array
    {
        if ($node) {
            self::$parsingNode = $node;
        } else {
            $node = self::$parsingNode;
        }
        $parent = $node?->parentNode;
        $parentTag = $parent instanceof \DOMElement ? $parent->tagName : null;
        if (! self::$wrapping && $node && $node instanceof \DOMElement && in_array($parentTag, ['body', 'ul', 'ol'])) {
            if ($node->tagName === 'ul' || $node->tagName === 'ol') {
                return null;
            }

            return [[
                'tag' => $node->tagName,
            ]];
        }

        return null;
    }

    /**
     * The `wrapper` function allows us to define the nested content of a node,
     * for that we run the node through the TipTap parser again, setting a
     * variable, so it knows to skip this node for the `parseHTML` function.
     *
     * @param  \DOMElement  $DOMNode
     * @return array<string, mixed>
     */
    public static function wrapper($DOMNode)
    {
        self::$wrapping = true;
        $parsed = self::parseNode($DOMNode);
        self::$wrapping = false;

        return $parsed;
    }

    /**
     * The `renderHTML` function has the confusing logic to account for the
     * `hasBullet` feature. Here we check if the `hasBullet` attribute is set
     * and then use the static `listStarted` property to know when to start and
     * end the list tags.
     *
     * @param  \stdClass  $node
     * @param  array<string, string>  $HTMLAttributes
     * @return array{content: string}|array{0: int}|array{0: string, 1: array<string, string>, 2: int}
     */
    public function renderHTML($node, $HTMLAttributes = [])
    {
        unset($HTMLAttributes['hasBullet']);
        if ($node->attrs->hasBullet) {
            $level = ($node->attrs->indent ?? 0) + 1;
            $html = '<li'.HTML::renderAttributes(HTML::mergeAttributes($HTMLAttributes)).'>'.self::serializeNode($node);
            if (self::$listLevel < $level) {
                while (self::$listLevel < $level) {
                    $html = '<ul>'.$html;
                    self::$listLevel++;
                }
            } elseif (self::$listLevel > $level) {
                while (self::$listLevel > $level) {
                    $html = '</li></ul>'.$html;
                    self::$listLevel--;
                }
            } else {
                $html = '</li>'.$html;
            }

            return ['content' => $html];
        }
        if (self::$listLevel) {
            $prefix = '';
            while (self::$listLevel) {
                $prefix = '</li></ul>'.$prefix;
                self::$listLevel--;
            }
            $html = self::serializeNode($node);
            if ($HTMLAttributes) {
                $html = '<div'.HTML::renderAttributes(HTML::mergeAttributes($HTMLAttributes)).'>'.$html.'</div>';
            }

            return ['content' => $prefix.$html];
        }
        // This bit is annoying. We only want to use a `div` tag when the
        // `indent` or `alignment` attributes are set, but the `$HTMLAttributes`
        // array is only passed here when building the opening tag. When the
        // serializer is building the closing tag it doesn't pass the attributes,
        // and so we need to set a static property to ensure we are returning
        // the same value for both the opening and closing tags.
        if ($HTMLAttributes || static::$openingDiv) {
            static::$openingDiv = ! static::$openingDiv;

            return ['div', HTML::mergeAttributes($HTMLAttributes), 0];
        }

        return [0];
    }

    /**
     * Here we take the node and pass it through the TipTap serializer to get
     * the nested HTML of the rootblock.
     *
     * @param  \stdClass  $node
     */
    protected static function serializeNode($node): string
    {
        $editor = TipTap::getEditor();
        $schema = $editor->schema;

        /** @phpstan-ignore-next-line This is fine */
        return (new DOMSerializer($schema))->process(json_decode(json_encode($node), true));
    }

    /**
     * Similar to the `serializeNode` function, this passes a serialized node
     * to the `DOMParser` to get the nested content of the rootblock.
     *
     * @param  \DOMElement  $node
     * @return array<string, mixed>
     */
    protected static function parseNode($node)
    {
        $editor = TipTap::getEditor();
        $schema = $editor->schema;
        $parser = new DOMParser($schema);
        $doc = $node->ownerDocument?->saveXML($node);
        if (! $doc) {
            throw new ShouldNotHappenException;
        }
        $parsed = $parser->process($doc);

        return $parsed['content'][0];
    }
}
