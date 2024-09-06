<?php

declare(strict_types=1);

namespace MarkupUtils\TipTapExtensions;

use Tiptap\Core\Extension;
use Tiptap\Utils\InlineStyle;

class Color extends Extension
{
    /**
     * @var string
     */
    public static $name = 'color';

    /**
     * @return array{types: string[]}
     */
    public function addOptions()
    {
        return [
            'types' => [
                'textStyle',
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function addGlobalAttributes()
    {
        return [
            [
                'types' => $this->options['types'],
                'attributes' => [
                    'color' => [
                        'default' => null,
                        'parseHTML' => static function ($DOMNode): ?string {
                            $attribute = InlineStyle::getAttribute($DOMNode, 'color');

                            if ($attribute === null) {
                                return null;
                            }

                            return preg_replace('/[\'"]+/', '', $attribute);
                        },
                        'renderHTML' => static function ($attributes): ?array {
                            if (! isset($attributes?->color)) {
                                return null;
                            }

                            return ['style' => "color: {$attributes->color}"];
                        },
                    ],
                ],
            ],
        ];
    }
}
