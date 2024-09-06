<?php

declare(strict_types=1);

namespace MarkupUtils;

class Plaintext extends Markup
{
    public function __construct(protected string $plaintext) {}

    public function convertToPlaintext(): self
    {
        return $this;
    }

    public function convertToHTML(): HTML
    {
        return new HTML("<p>$this->plaintext</p>");
    }

    public function convertToMarkdown(): Markdown
    {
        return new Markdown($this->plaintext);
    }

    public function convertToDelta(): Delta
    {
        return new Delta([['insert' => $this->plaintext]]);
    }

    public function convertToTipTap(): TipTap
    {
        return $this->convertToHTML()->convertToTipTap();
    }

    public function __toString(): string
    {
        return $this->plaintext;
    }
}
