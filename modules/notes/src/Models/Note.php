<?php

declare(strict_types=1);

namespace Notes\Models;

use MarkupUtils\HTML;
use MarkupUtils\Delta;
use MarkupUtils\Markup;
use MarkupUtils\TipTap;
use MarkupUtils\Markdown;
use MarkupUtils\Plaintext;
use MarkupUtils\MarkupType;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Note
 *
 * @property string $name
 * @property \MarkupUtils\Markup $text
 * @property \MarkupUtils\HTML $html
 * @property \MarkupUtils\Delta $delta
 * @property \MarkupUtils\TipTap $tiptap
 * @property \MarkupUtils\Plaintext $plaintext
 * @property \MarkupUtils\Markdown $markdown
 * @property \Illuminate\Support\Carbon $favorited_at
 *
 * Relationships
 * @property \Notes\Models\Notebook $notebook
 */
class Note extends Model
{
    use HasGlobalId;

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'favorited_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ]);
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'text',
            'markdown',
            'html',
            'delta',
            'tiptap',
            'plaintext',
            'favorited_at',
        ]);
    }

    public function isFavorite(): bool
    {
        return $this->favorited_at !== null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Notes\Models\Notebook, \Notes\Models\Note>
     */
    public function notebook(): BelongsTo
    {
        return $this->belongsTo(config('notes.models.notebook'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Markup, Markup>
     */
    public function text(): Attribute
    {
        return (new Attribute(
            get: function (string $value): Markup {
                return match ($this->format()) {
                    MarkupType::DELTA => new Delta($this->fromJson($value)),
                    MarkupType::HTML => new HTML($value),
                    MarkupType::MARKDOWN => new Markdown($value),
                    MarkupType::PLAINTEXT => new Plaintext($value),
                    MarkupType::TIPTAP => new TipTap($this->fromJson($value)),
                };
            },
            set: function (Markup $value): string {
                if ($value instanceof HTML) {
                    $value->clean();
                }

                return (string) $value->convertTo($this->format());
            }
        ))->withoutObjectCaching();
    }

    public function format(): MarkupType
    {
        return config('notes.format');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\MarkupUtils\HTML, string>
     */
    public function html(): Attribute
    {
        return (new Attribute(
            get: fn () => $this->text->convertToHTML(),
            set: fn (string $html) => ['text' => (string) (new HTML($html))->clean()->convertTo($this->format())],
        ))->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\MarkupUtils\Plaintext, string>
     */
    public function plaintext(): Attribute
    {
        return (new Attribute(
            get: fn () => $this->text->convertToPlaintext(),
            set: fn (string $plaintext) => ['text' => (string) (new Plaintext($plaintext))->convertTo($this->format())],
        ))->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\MarkupUtils\Markdown, string>
     */
    public function markdown(): Attribute
    {
        return (new Attribute(
            get: fn () => $this->text->convertToMarkdown(),
            set: fn (string $markdown) => ['text' => (string) (new Markdown($markdown))->convertTo($this->format())],
        ))->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\MarkupUtils\Delta, array>
     */
    public function delta(): Attribute
    {
        return (new Attribute(
            get: fn () => $this->text->convertToDelta(),
            set: fn (?array $delta) => ['text' => (string) (new Delta($delta ?? ['ops' => []]))->convertTo($this->format())],
        ))->withoutObjectCaching();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\MarkupUtils\TipTap, array>
     */
    public function tiptap(): Attribute
    {
        return (new Attribute(
            get: fn () => $this->text->convertToTipTap(),
            set: fn (array $tiptap) => ['text' => (string) (new TipTap($tiptap))->convertTo($this->format())],
        ))->withoutObjectCaching();
    }
}
