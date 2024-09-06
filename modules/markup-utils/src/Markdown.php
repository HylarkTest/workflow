<?php

declare(strict_types=1);

namespace MarkupUtils;

use League\CommonMark\Node\Node;
use League\CommonMark\CommonMarkConverter;
use MarkupUtils\Converters\MarkdownToDelta;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Environment\Environment;
use MarkupUtils\Converters\MarkdownToPlaintext;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

class Markdown extends Markup
{
    public function __construct(protected string $markdown) {}

    public function getTree(): Node
    {
        return $this->getParser()->parse($this->markdown);
    }

    public function convertToPlaintext(): Plaintext
    {
        return MarkdownToPlaintext::convert($this);
    }

    public function convertToHTML(): HTML
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return new HTML($converter->convert($this->markdown)->getContent());
    }

    public function convertToMarkdown(): self
    {
        return $this;
    }

    public function convertToDelta(): Delta
    {
        return MarkdownToDelta::convert($this);
    }

    public function convertToTipTap(): TipTap
    {
        return $this->convertToHTML()->convertToTipTap();
    }

    public function __toString(): string
    {
        return $this->markdown;
    }

    protected function getParser(): MarkdownParser
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);

        return new MarkdownParser($environment);
    }
}
