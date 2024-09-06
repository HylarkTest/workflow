<?php

declare(strict_types=1);

namespace MarkupUtils;

use Mews\Purifier\Purifier;
use League\HTMLToMarkdown\HtmlConverter;

class HTML extends Markup
{
    public function __construct(protected string $html) {}

    public function convertToPlaintext(): Plaintext
    {
        // First we need to remove any style and script tags.
        // Now you must never use regex to manipulate HTML, so this is almost
        // certainly a bad idea. But it is also superfast, and what's the worst
        // that could happen?
        // If this does end up being an issue, the other way to do this would
        // be to use Symfony's DOMCrawler class to strip them out.
        /** @var string $html */
        $html = preg_replace('/<(style|script)[^>]+>[^>]+<\/(style|script)>/', '', $this->html);

        return new Plaintext(trim(html_entity_decode(strip_tags($this->br2nl($html)), \ENT_QUOTES | \ENT_HTML5)));
    }

    public function convertToHTML(): self
    {
        return $this;
    }

    public function convertToMarkdown(): Markdown
    {
        return new Markdown((new HtmlConverter(['header_style' => 'atx']))->convert($this->html));
    }

    public function convertToDelta(): Delta
    {
        return $this->convertToMarkdown()->convertToDelta();
    }

    public function convertToTipTap(): TipTap
    {
        $tiptap = TipTap::getEditor()->setContent($this->html);

        return new TipTap($tiptap->getDocument());
    }

    public function clean(): self
    {
        $this->html = resolve(Purifier::class)->clean($this->html);

        return $this;
    }

    public function __toString(): string
    {
        return $this->html;
    }

    protected function br2nl(string $value): string
    {
        return preg_replace('/<br[^>]*>/i', "\n", $value) ?: $value;
    }
}
