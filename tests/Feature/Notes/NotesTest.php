<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Image;
use App\Models\Notebook;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('notebooks can be fetched from the api', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    /** @var \App\Models\Notebook $notebook */
    $notebook = createNotebook($user);
    /** @var \App\Models\Note $note */
    $note = $notebook->notes()->create([
        'name' => 'First note',
        'markdown' => <<<'MD'
# The title of the note
- Some list
- and another

_All done_
MD,
    ]);

    $this->be($user)->graphQL("
    {
        notebook(id: \"$notebook->global_id\") {
            id
            name
            notesCount
            notes {
                edges {
                    node {
                        id
                        name
                        html
                        plaintext
                        markdown
                        delta
                        tiptap
                    }
                }
            }
        }
    }
    ")->assertJson(['data' => ['notebook' => [
        'id' => $notebook->global_id,
        'name' => $notebook->name,
        'notesCount' => 1,
        'notes' => ['edges' => [['node' => [
            'id' => $note->global_id,
            'name' => $note->name,
            'html' => <<<'HTML'
<h1>The title of the note</h1><ul><li><p>Some list</p></li><li><p>and another</p></li></ul><p><em>All done</em></p>
HTML,
            'plaintext' => "The title of the note\n\nSome list\n\nand another\n\nAll done\n",
            'markdown' => '# The title of the note

- Some list
- and another

*All done*',
            'delta' => ['ops' => [
                ['insert' => 'The title of the note'],
                ['insert' => "\n", 'attributes' => ['header' => 1]],
                ['insert' => "\n"],
                ['insert' => 'Some list'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['insert' => 'and another'],
                ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
                ['insert' => "\n"],
                ['attributes' => ['italic' => true], 'insert' => 'All done'],
                ['insert' => "\n"],
            ]],
            'tiptap' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'The title of the note']]]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Some list']]]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => true],
                        'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'and another']]]],
                    ],
                    [
                        'type' => 'rootblock',
                        'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                        'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'All done', 'marks' => [['type' => 'italic']]]]]],
                    ],
                ],
            ],
        ]]]],
    ]]], true);
});

test('a user can create a note', function () {
    $user = createUser();
    $notebook = createNotebook($user);

    $this->be($user)->assertGraphQLMutation(
        'createNote(input: $input)',
        ['input: CreateNoteInput!' => [
            'name' => 'New note',
            'tiptap' => ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world']]]]],
            'notebookId' => $notebook->globalId(),
        ]],
    );

    $note = $notebook->notes->first();
    expect($note)->not->toBeNull()
        ->and($note->tiptap->toArray())->toBe(['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world']]]]]);
});

test('a note with an image is saved to amazon', function () {
    fakeStorage('images');
    $user = createUser();
    $notebook = createNotebook($user);

    $file = UploadedFile::fake()->image('image.jpg');
    $data = $file->getContent();
    $imageData = 'data:image/jpeg;base64,'.base64_encode($data);

    $this->be($user)->assertGraphQLMutation(
        'createNote(input: $input)',
        ['input: CreateNoteInput!' => [
            'name' => 'New note',
            'tiptap' => ['type' => 'doc', 'content' => [['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $imageData]]]]]],
            'notebookId' => $notebook->globalId(),
        ]],
    );

    $image = Image::latest()->first();
    $note = $notebook->notes->first();
    $imageSrc = $note->tiptap->tiptap['content'][0]['content'][0]['attrs']['src'];
    expect($imageSrc)->toBe($image->url());
});

test('images removed from notes are removed from amazon', function () {
    fakeStorage('images');
    $user = createUser();
    $notebook = createNotebook($user);

    $file = UploadedFile::fake()->image('image.jpg');
    $image = Image::createFromFile($file);
    $note = $notebook->notes()->create([
        'name' => 'New note',
        'tiptap' => ['type' => 'doc', 'content' => [['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $image->url()]]]]]],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'updateNote(input: $input)',
        ['input: UpdateNoteInput!' => [
            'id' => $note->global_id,
            'name' => 'New note',
            'tiptap' => ['type' => 'doc', 'content' => [['type' => 'rootblock', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world']]]]]]],
        ]],
    );

    expect($image->fresh())->toBeNull();
});

test('images can be added to notes with existing images', function () {
    fakeStorage('images');
    $user = createUser();
    $notebook = createNotebook($user);

    $file = UploadedFile::fake()->image('image.jpg');
    $image = Image::createFromFile($file);
    $note = $notebook->notes()->create([
        'name' => 'New note',
        'tiptap' => ['type' => 'doc', 'content' => [['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $image->url()]]]]]],
    ]);

    $newFile = UploadedFile::fake()->image('image2.jpg');
    $newImageData = 'data:image/jpeg;base64,'.base64_encode($newFile->getContent());

    $this->be($user)->assertGraphQLMutation(
        'updateNote(input: $input)',
        ['input: UpdateNoteInput!' => [
            'id' => $note->global_id,
            'name' => 'New note',
            'tiptap' => ['type' => 'doc', 'content' => [
                ['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $image->url()]]]],
                ['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $newImageData]]]],
            ]],
        ]],
    );

    expect(Image::count())->toBe(2);
    expect($note->fresh()->tiptap->tiptap['content'])->toHaveCount(2);
});

test('images are deleted when a note is deleted', function () {
    fakeStorage('images');
    $user = createUser();
    $notebook = createNotebook($user);

    $file = UploadedFile::fake()->image('image.jpg');
    $image = Image::createFromFile($file);
    $note = $notebook->notes()->create([
        'name' => 'New note',
        'tiptap' => ['type' => 'doc', 'content' => [['type' => 'rootblock', 'content' => [['type' => 'image', 'attrs' => ['src' => $image->url()]]]]]],
    ]);

    $note->delete();
    expect($image->fresh())->not->toBeNull();

    $note->forceDelete();
    expect($image->fresh())->toBeNull();
});

test('whitespaces are not trimmed in notes', function () {
    $user = createUser();
    $notebook = createNotebook($user);

    $this->be($user)->assertGraphQLMutation(
        'createNote(input: $input)',
        ['input: CreateNoteInput!' => [
            'name' => 'New note',
            'delta' => ['ops' => [['insert' => 'Hello world'], ['insert' => "\n", 'attributes' => ['header' => 1]]]],
            'notebookId' => $notebook->globalId(),
        ]],
    );

    $note = $notebook->notes->first();
    expect($note)->not->toBeNull()
        ->and($note->delta->toArray())->toBe(['ops' => [['insert' => 'Hello world'], ['insert' => "\n", 'attributes' => ['header' => 1]]]]);
});

test('a default notebook is created if not there', function () {
    $user = createUser();

    $this->be($user)->assertGraphQL([
        'notebooks' => [
            'edges' => [
                ['node' => [
                    'name' => 'General',
                ]],
            ],
        ],
    ]);
});

test('a user can see their notebooks', function () {
    $user = createUser();

    $firstNotebook = createNotebook($user);
    $secondNotebook = createNotebook($user);

    $this->be($user)->assertGraphQL([
        'notebooks' => [
            'edges' => [
                ['node' => [
                    'id' => $firstNotebook->globalId(),
                ]],
                ['node' => [
                    'id' => $secondNotebook->globalId(),
                ]],
            ],
        ],
    ]);
});

test('note actions are generated', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $this->be($user);

    $notebook = createNotebook($user, ['name' => 'General']);
    /** @var \App\Models\Note $note */
    $note = $notebook->notes()->create(['name' => 'New note', 'plaintext' => 'All good things']);

    $this->graphQL("
    {
        history(forNode: \"$note->global_id\") {
            edges {
                node {
                    description
                    changes {
                        description
                        after
                        before
                        type
                    }
                }
            }
        }
    }
    ")->assertJsonCount(1, 'data.history.edges')
        ->assertJson(['data' => ['history' => ['edges' => [
            ['node' => ['changes' => [
                [
                    'description' => 'Added the name',
                    'before' => null,
                    'after' => 'New note',
                ],
                [
                    'description' => 'Added the text',
                    'before' => null,
                    'after' => "All good things\n",
                ],
                [
                    'description' => 'Created on notebook',
                    'before' => null,
                    'after' => 'General',
                ],
            ]]],
        ]]]]);
});

test('notes can be created', function () {
    $user = createUser();
    $notebook = createNotebook($user);

    $this->be($user)->graphQL('
    mutation CreateNote($input: CreateNoteInput!) {
        createNote(input: $input) {
            code
            success
            note {
                id
            }
            notebook {
                id
                notesCount
            }
        }
    }
    ', ['input' => [
        'notebookId' => $notebook->globalId,
        'name' => 'First note',
        'markdown' => <<<'MD'
# This is my first note
It's a really good one
You'll like it.
MD,
    ]])->assertSuccessfulGraphQL();

    expect($notebook->notes)->toHaveCount(1);
    $note = $notebook->notes->first();
    expect($note->name)->toBe('First note');
    static::assertSame([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 1],
                        'content' => [
                            ['type' => 'text', 'text' => 'This is my first note'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'rootblock',
                'attrs' => ['alignment' => 'left', 'indent' => 0, 'hasBullet' => false],
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => "It's a really good one\nYou'll like it."],
                        ],
                    ],
                ],
            ],
        ],
    ], $note->text->toArray());
});

test('only one content field can be submitted', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $notebook = createNotebook($user);

    $query = '
    mutation CreateNote($input: CreateNoteInput!) {
        createNote(input: $input) {
            code
            success
            note {
                id
            }
            notebook {
                id
                notesCount
            }
        }
    }
    ';

    $this->be($user)->graphQL($query, ['input' => [
        'notebookId' => $notebook->globalId,
        'name' => 'First note',
        'html' => '<p>Hello there</p>',
        'markdown' => '# Hello there',
    ]])
        ->assertGraphQLValidationError('input.html', 'The HTML field prohibits markdown / delta / plain text / tiptap from being present.')
        ->assertGraphQLValidationError('input.markdown', 'The markdown field prohibits HTML / delta / plain text / tiptap from being present.');
});

test('there is a size limit on notes', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $notebook = createNotebook($user);

    $query = '
    mutation CreateNote($input: CreateNoteInput!) {
        createNote(input: $input) {
            code
            success
            note {
                id
            }
            notebook {
                id
                notesCount
            }
        }
    }
    ';

    $longString = str_pad('', 10_005, 'A');

    $this->be($user)->graphQL($query, ['input' => [
        'notebookId' => $notebook->globalId,
        'name' => 'First note',
        'html' => '<p>'.$longString.'</p>',
    ]])->assertGraphQLValidationError('input.html', 'The HTML must not be greater than 10000 characters.');
    $this->be($user)->graphQL($query, ['input' => [
        'notebookId' => $notebook->globalId,
        'name' => 'First note',
        'delta' => ['ops' => [['insert' => $longString]]],
    ]])->assertGraphQLValidationError('input.delta', 'Notes can have a maximum of 10 000 characters. Please shorten your note.');
});

// Helpers
function createNotebook(User $owner, $attributes = []): Notebook
{
    $base = $owner->firstPersonalBase();

    return $base->notebooks()->create(array_merge([
        'space_id' => $base->spaces()->first()->id,
        'name' => 'First notebook',
    ], $attributes));
}
