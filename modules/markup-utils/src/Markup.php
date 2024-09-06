<?php

declare(strict_types=1);

namespace MarkupUtils;

abstract class Markup
{
    public function convertTo(MarkupType $type): self
    {
        return match ($type) {
            MarkupType::PLAINTEXT => $this->convertToPlaintext(),
            MarkupType::HTML => $this->convertToHTML(),
            MarkupType::MARKDOWN => $this->convertToMarkdown(),
            MarkupType::DELTA => $this->convertToDelta(),
            MarkupType::TIPTAP => $this->convertToTipTap(),
        };
    }

    public function textLength(): int
    {
        return mb_strlen((string) $this->convertToPlaintext());
    }

    abstract public function convertToPlaintext(): Plaintext;

    abstract public function convertToHTML(): HTML;

    abstract public function convertToMarkdown(): Markdown;

    abstract public function convertToDelta(): Delta;

    abstract public function convertToTipTap(): TipTap;

    abstract public function __toString(): string;
}
