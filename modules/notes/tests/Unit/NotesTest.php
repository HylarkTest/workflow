<?php

declare(strict_types=1);

namespace Tests\Notes\Unit;

use MarkupUtils\HTML;
use MarkupUtils\Delta;
use Notes\Models\Note;
use MarkupUtils\TipTap;
use Tests\Notes\TestCase;
use MarkupUtils\MarkupType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    protected array $testDelta = [
        'ops' => [
            ['insert' => 'Gandalf', 'attributes' => ['bold' => true]],
            ['insert' => ' the '],
            ['insert' => 'Grey', 'attributes' => ['color' => '#cccccc']],
        ],
    ];

    protected array $testTiptap = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'rootblock',
                'attrs' => ['bulletList' => null, 'indent' => 0, 'align' => 'left'],
                'content' => [['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'Gandalf'],
                    ['type' => 'text', 'text' => 'the'],
                    ['type' => 'text', 'marks' => [['type' => 'highlight', 'attrs' => ['color' => '#b6c0c8']]], 'text' => 'Grey'], ], ],
                ],
            ],
        ],
    ];

    /**
     * HTML notes are purified when they are stored
     *
     * @test
     */
    public function html_notes_are_purified_when_they_are_stored(): void
    {
        config(['notes.format' => MarkupType::HTML]);

        $safeNote = Note::query()->create([
            'name' => 'Note',
            'text' => new HTML('<div>Hello there</div>'),
        ]);

        $maliciousNote = Note::query()->create([
            'name' => 'Note2',
            'text' => new HTML('<div>Nothing to see here</div><script>console.log(\'evil\')</script>'),
        ]);

        static::assertSame('<div>Hello there</div>', $safeNote->fresh()->getAttributes()['text']);
        static::assertSame('<div>Nothing to see here</div>', $maliciousNote->fresh()->getAttributes()['text']);
    }

    /**
     * Delta notes are serialized correctly
     *
     * @test
     */
    public function delta_notes_are_serialized_correctly(): void
    {
        config(['notes.format' => MarkupType::DELTA]);

        $deltaNote = Note::query()->create([
            'name' => 'Note',
            'text' => new Delta($this->testDelta),
        ]);

        static::assertSame(json_encode($this->testDelta, \JSON_THROW_ON_ERROR, 512), $deltaNote->fresh()->getAttributes()['text']);
        static::assertSame($this->testDelta, $deltaNote->fresh()->delta->toArray());
    }

    /**
     * Delta notes are serialized correctly
     *
     * @test
     */
    public function tiptap_notes_are_serialized_correctly(): void
    {
        config(['notes.format' => MarkupType::TIPTAP]);

        $tiptapNote = Note::query()->create([
            'name' => 'Note',
            'text' => new TipTap($this->testTiptap),
        ]);

        static::assertSame(json_encode($this->testTiptap, \JSON_THROW_ON_ERROR, 512), $tiptapNote->fresh()->getAttributes()['text']);
        static::assertSame($this->testTiptap, $tiptapNote->fresh()->tiptap->toArray());
    }
}
