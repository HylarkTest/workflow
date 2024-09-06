<?php

declare(strict_types=1);

namespace MarkupUtils;

/**
 * @phpstan-import-type DeltaOps from \MarkupUtils\Delta
 */
enum MarkupType: string
{
    /**
     * @param  string|DeltaOps  $value
     *
     * @throws \JsonException
     */
    public function createMarkup(string|array $value): Markup
    {
        return match (true) {
            $this === self::HTML && \is_string($value) => new HTML($value),
            $this === self::MARKDOWN && \is_string($value) => new Markdown($value),
            $this === self::DELTA => new Delta(\is_string($value) ? json_decode($value, true, 512, \JSON_THROW_ON_ERROR) : $value),
            $this === self::PLAINTEXT && \is_string($value) => new Plaintext($value),
            $this === self::TIPTAP => new TipTap(\is_string($value) ? json_decode($value, true, 512, \JSON_THROW_ON_ERROR) : $value),
            default => throw new \InvalidArgumentException('Note type ['.\gettype($value).'] is not compatible with format ['.$this->value.']'),
        };
    }
    case PLAINTEXT = 'PLAINTEXT';
    case HTML = 'HTML';
    case MARKDOWN = 'MARKDOWN';
    case DELTA = 'DELTA';
    case TIPTAP = 'TIPTAP';
}
