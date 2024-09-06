<?php

declare(strict_types=1);

namespace MarkupUtils\TipTapExtensions;

class Paragraph extends \Tiptap\Nodes\Paragraph
{
    /**
     * @return array<array{tag: string, attrs?: array<string, mixed>}>
     */
    public function parseHTML()
    {
        return [
            ...parent::parseHTML(),
            ['tag' => 'li'],
        ];
    }
}
