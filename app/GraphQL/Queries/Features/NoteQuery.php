<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use MarkupUtils\TipTap;
use App\Core\Features\Repositories\NoteItemRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListItemQuery<\App\Models\Note, \App\Models\Notebook>
 */
class NoteQuery extends FeatureListItemQuery
{
    protected function getCreateDataKeys(): array
    {
        return [
            'name',
            'html',
            'markdown',
            'delta',
            'tiptap',
            'plaintext',
        ];
    }

    protected function getUpdateDataKeys(): array
    {
        return [
            'name',
            'markdown',
            'delta',
            'tiptap',
            'html',
            'plaintext',
            'isFavorite',
        ];
    }

    protected function repository(): NoteItemRepository
    {
        return resolve(NoteItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'notebook';
    }

    protected function getItemKey(): string
    {
        return 'note';
    }

    protected function validateData(Base $base, array $data): void
    {
        $this->validateAccountLimits($base);

        if (array_key_exists('tiptap', $data)) {
            $this->validateTiptapContent($data);
        }
    }

    private function validateTiptapContent(array $data): void
    {
        $min = 1;
        $tiptapContent = $data['tiptap'] ?? null;

        if ($tiptapContent === null || $this->getTiptapContentLength($tiptapContent) < $min) {
            $this->throwValidationException(
                'input.tiptap',
                trans('validation.min.string', ['attribute' => 'content', 'min' => $min])
            );
        }
    }

    private function getTiptapContentLength(array $content): int
    {
        return (new TipTap($content))->textLength();
    }

    private function validateAccountLimits(Base $base): void
    {
        if (! $base->accountLimits()->canCreateNotes()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
