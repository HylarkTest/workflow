<?php

declare(strict_types=1);

namespace MarkupUtils;

use nadar\quill\Lexer;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, array>
 *
 * @phpstan-type DeltaAttributes array<string, mixed>
 * @phpstan-type DeltaEmbed array{ image: string }
 * @phpstan-type DeltaDivider array{ divider: bool }
 * @phpstan-type DeltaInsert string|DeltaEmbed|DeltaDivider
 * @phpstan-type DeltaOp array{ insert: DeltaInsert, attributes?: DeltaAttributes }
 * @phpstan-type DeltaOps array{ ops: array<int, DeltaOp> }
 */
class Delta extends Markup implements Arrayable
{
    /**
     * @var \Illuminate\Support\Collection<int, DeltaOp>
     */
    protected Collection $ops;

    /**
     * @param  DeltaOps|array<int, DeltaOp>  $ops
     */
    public function __construct(array $ops = [])
    {
        /** @var array<int, DeltaOp> $ops */
        $ops = $ops['ops'] ?? $ops;
        $this->ops = Collection::make($ops);
    }

    /**
     * @param  DeltaInsert  $value
     * @param  DeltaAttributes|null  $attributes
     * @return $this
     */
    public function insert(string|array $value, ?array $attributes = []): static
    {
        $insert = ['insert' => $value];

        if ($attributes) {
            $insert['attributes'] = $attributes;
        }

        $this->ops->push($insert);

        return $this;
    }

    public function concat(self $delta): static
    {
        $this->ops = $this->ops->concat($delta->getOps());

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeltaOp>
     */
    public function getOps(): Collection
    {
        return $this->ops;
    }

    /**
     * @return DeltaOps
     */
    public function toArray(): array
    {
        return ['ops' => $this->ops->all()];
    }

    public function convertToPlaintext(): Plaintext
    {
        $plaintext = $this->ops
            ->pluck('insert')
            ->filter(fn ($insert) => \is_string($insert))
            ->implode('');

        return new Plaintext($plaintext);
    }

    public function convertToHTML(): HTML
    {
        return new HTML((new Lexer($this->toArray()))->render() ?: '<p></p>');
    }

    public function convertToMarkdown(): Markdown
    {
        return $this->convertToHTML()->convertToMarkdown();
    }

    public function convertToDelta(): self
    {
        return $this;
    }

    public function convertToTipTap(): TipTap
    {
        return $this->convertToHTML()->convertToTipTap();
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), \JSON_THROW_ON_ERROR);
    }
}
