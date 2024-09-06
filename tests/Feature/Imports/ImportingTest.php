<?php

declare(strict_types=1);

use App\Models\Import;
use App\Core\TaskStatus;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\artisan;

use Illuminate\Support\Facades\Event;
use App\Core\Imports\ImportItemStatus;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Queue\Console\WorkCommand;
use App\Core\Imports\ImportFileRepository;
use App\Events\Core\ProgressTrackerUpdated;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    fakeStorage('tmp');
});

test('a user can import a spreadsheet with a column map', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Multi', 'type' => FieldType::MULTI(), 'options' => ['fields' => [
            ['name' => 'Profession', 'type' => FieldType::LINE()],
        ]]],
        ['name' => 'Emails', 'type' => FieldType::EMAIL(), 'options' => ['list' => ['max' => 5]]],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $nameField = $mapping->fields[0];
    $multiField = $mapping->fields[1];
    $professionField = $multiField->fields()[0];
    $emailsField = $mapping->fields[2];

    config(['hylark.imports.chunk_size' => 2]);

    Event::fake(ProgressTrackerUpdated::class);

    useDatabaseQueue();
    $id = $this->be($user)->assertGraphQLMutation(
        ['importSpreadsheet(input: $input)' => [
            'code' => '200',
            'import' => [
                'id' => new Ignore,
                'name' => 'Untitled',
                'progress' => [
                    'status' => 'STARTED',
                ],
            ],
        ]],
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $nameField->id()],
                ['column' => 1, 'fieldId' => $multiField->id().'.'.$professionField->id()],
                ['column' => 2, 'fieldId' => $emailsField->id()],
                ['column' => 3, 'fieldId' => $emailsField->id()],
            ],
        ]]
    )->json('data.importSpreadsheet.import.id');

    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'default'])->run(); // Count rows job
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // QueueImport.php
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // ChunkRead.php

    /** @var \App\Models\Import $import */
    $import = find($id);
    expect($import->taskProgress())
        ->toHaveKey('status', TaskStatus::STARTED)
        ->toHaveKey('progress', 0.4)
        ->toHaveKey('processedCount', 2)
        ->toHaveKey('totalCount', 5);

    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // ChunkRead.php
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // ChunkRead.php

    expect($import->importables)
        ->each(function ($importMap, $index) {
            $importMap->status->toBe(ImportItemStatus::IMPORTED)
                ->row->toBe($index + 2);
        });

    $progress = 0.0;
    Event::assertDispatched(ProgressTrackerUpdated::class, function (ProgressTrackerUpdated $event) use (&$progress) {
        if ($event->progress !== $progress) {
            return false;
        }
        $progress += 0.4;
        if ($progress > 1) {
            $progress = 1.0;
        }

        return true;
    });

    $items = $mapping->items;

    expect($items)->toHaveCount(5);

    $this->assertGraphQL(['items' => [$mapping->apiName => ['edges' => [
        ['node' => [
            'name' => 'Luke Skywalker',
            'data' => [
                $nameField->apiName => ['fieldValue' => 'Luke Skywalker'],
                $multiField->apiName => ['fieldValue' => [
                    $professionField->apiName => ['fieldValue' => 'Jedi'],
                ]],
                $emailsField->apiName => ['listValue' => [
                    ['fieldValue' => 'ls@rebels.com'],
                    ['fieldValue' => 'ls@jedi.com'],
                ]],
            ],
        ]],
        ['node' => [
            'name' => 'Padmé Amidala',
            'data' => [
                $nameField->apiName => ['fieldValue' => 'Padmé Amidala'],
                $multiField->apiName => ['fieldValue' => [
                    $professionField->apiName => ['fieldValue' => 'Senator'],
                ]],
                $emailsField->apiName => ['listValue' => [
                    ['fieldValue' => 'pa@senate.com'],
                ]],
            ],
        ]],
        ['node' => [
            'name' => 'Obi-wan Kenobi',
            'data' => [
                $nameField->apiName => ['fieldValue' => 'Obi-wan Kenobi'],
                $multiField->apiName => ['fieldValue' => [
                    $professionField->apiName => ['fieldValue' => 'Jedi'],
                ]],
                $emailsField->apiName => ['listValue' => [
                    ['fieldValue' => 'ok@jedi.com'],
                    ['fieldValue' => 'ben@tatooine.com'],
                ]],
            ],
        ]],
        ['node' => [
            'name' => 'Leia Organa',
            'data' => [
                $nameField->apiName => ['fieldValue' => 'Leia Organa'],
                $multiField->apiName => ['fieldValue' => [
                    $professionField->apiName => ['fieldValue' => 'Princess'],
                ]],
                $emailsField->apiName => ['listValue' => [
                    ['fieldValue' => 'lo@senate.com'],
                ]],
            ],
        ]],
        ['node' => [
            'name' => 'Anakin Skywalker',
            'data' => [
                $nameField->apiName => ['fieldValue' => 'Anakin Skywalker'],
                $multiField->apiName => ['fieldValue' => [
                    $professionField->apiName => ['fieldValue' => 'Jedi'],
                ]],
                $emailsField->apiName => ['listValue' => [
                    ['fieldValue' => 'as@jedi.com'],
                    ['fieldValue' => 'dv@sith.com'],
                ]],
            ],
        ]],
    ]]]]);
});

test('imported records have performers', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $nameField = $mapping->fields[0];

    useDatabaseQueue(); // Using queue because the queue runner is reason performer isn't added by default
    $this->be($user)->assertGraphQLMutation(
        ['importSpreadsheet(input: $input)' => [
            'code' => '200',
            'import' => [
                'id' => new Ignore,
                'name' => 'Untitled',
                'progress' => [
                    'status' => 'STARTED',
                ],
            ],
        ]],
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $nameField->id()],
            ],
        ]]
    );

    enableAllActions();

    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'default'])->run(); // Count rows job
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // QueueImport.php
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run(); // ChunkRead.php

    $items = $mapping->items;

    expect($items)->toHaveCount(5);
    $item = $items->first();

    expect($item->actions)->toHaveCount(1);
    expect($item->createAction->performer)
        ->not->toBeNull()
        ->id->toBe($user->id);
});

test('a user can subscribe to the progress of an import', function () {
    $user = createUser();
    $file = UploadedFile::fake()->create('import.csv', testCSV());
    $fileRepository = resolve(ImportFileRepository::class);
    $fileRepository->storeTemporaryFile($file);
    $import = Import::startImport('Untitled', $user->firstPersonalBase()->pivot, $file, true);

    $channel = $this->be($user)->graphQL(/** @lang GraphQL */ '
        subscription {
            progressTrackerUpdated(taskId: "'.$import->taskId().'") {
                id
                status
                progress
                estimatedTimeRemaining
            }
        }'
    )->json('extensions.lighthouse_subscriptions.channel');

    config([
        'broadcasting.connections.pusher.key' => 'abc',
        'broadcasting.connections.pusher.secret' => 'abc',
        'broadcasting.connections.pusher.app_id' => 'abc',
    ]);

    auth()->logout();
    $this->postJson(
        route('lighthouse.subscriptions.auth'),
        ['channel_name' => $channel, 'socket_id' => '123.123'],
        ['X-Base-Id' => $user->firstPersonalBase()->global_id],
    )->assertSuccessful();
});

test('the column map must include valid field ids', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $this->be($user)->assertFailedGraphQLMutation(
        'importSpreadsheet(input: $input).code',
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => 'abc123'],
            ],
        ]]
    )->assertGraphQLValidationError('input.columnMap.0.fieldId', 'Field with ID abc123 does not exist in the mapping.');
});

test('the import file must be a valid document', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);

    $file = UploadedFile::fake()->image('import.jpg');

    $this->be($user)->assertFailedGraphQLMutation(
        'importSpreadsheet(input: $input).code',
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields->first()->id()],
            ],
        ]]
    )->assertGraphQLValidationError('input.file', 'The file field must have one of the following extensions: csv, tsv, xls, xlsx, ods.');
});

test('an import can be reverted', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $id = $this->be($user)->assertGraphQLMutation(
        ['importSpreadsheet(input: $input)' => [
            'code' => '200',
            'import' => [
                'id' => new Ignore,
                'name' => 'Untitled',
                'progress' => [
                    'status' => 'STARTED',
                ],
            ],
        ]],
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields->first()->id()],
            ],
        ]]
    )->json('data.importSpreadsheet.import.id');

    $items = $mapping->items;
    expect($items)->toHaveCount(5);

    useDatabaseQueue();
    config(['hylark.imports.revert_chunk_size' => 2]);
    $this->be($user)->assertGraphQLMutation(
        'revertImport(input: $input)',
        ['input: RevertImportInput!' => [
            'id' => $id,
        ]]
    );

    /** @var \App\Models\Import $import */
    $import = find($id);

    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run();

    expect($import->taskProgress())
        ->toHaveKey('status', TaskStatus::REVERTING)
        ->toHaveKey('progress', 0.4)
        ->toHaveKey('totalCount', 5);

    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run();
    artisan(WorkCommand::class, ['--once' => true, '--queue' => 'imports'])->run();

    $import->refresh();
    expect($mapping->items()->count())->toBe(0);
    expect($import->status)->toBe(TaskStatus::REVERTED);
    expect($import->importables)->toHaveCount(5)
        ->each(function ($importMap) {
            $importMap->status->toBe(ImportItemStatus::REVERTED)
                ->importable_id->toBeNull()
                ->importable_type->toBe('items');
        });
});

test('an import keeps track of rows that failed', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Profession', 'type' => FieldType::LINE()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', <<<'CSV'
Full name,Profession
Leia Organa,Princess
,Jedi
CSV);

    $id = $this->be($user)->assertGraphQLMutation(
        ['importSpreadsheet(input: $input)' => [
            'code' => '200',
            'import' => ['id' => new Ignore],
        ]],
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 1, 'fieldId' => $mapping->fields[1]->id()],
            ],
        ]],
    )->json('data.importSpreadsheet.import.id');

    /** @var \App\Models\Import $import */
    $import = find($id);
    expect($mapping->items)->toHaveCount(1);
    expect($import->importables)->toHaveCount(2);
    expect($import->importables->first()->status)->toBe(ImportItemStatus::IMPORTED);
    expect($import->importables->last()->status)->toBe(ImportItemStatus::FAILED);
});

test('really old dates can be imported from excel', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Date', 'type' => FieldType::DATE()],
    ]]);

    $file = new UploadedFile(
        __DIR__.'/../../resources/imports/old_date_import.xlsx',
        'contacts.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $this->be($user)->assertGraphQLMutation(
        ['importSpreadsheet(input: $input)' => [
            'code' => '200',
            'import' => ['id' => new Ignore],
        ]],
        ['input: ImportSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
            'columnMap' => $mapping->fields->map(fn (Field $field, $column) => ['column' => $column, 'fieldId' => $field->id()])->all(),
            'dateFormat' => 'd/m/y',
        ]],
    );

    $this->assertGraphQL(['items' => [$mapping->apiName => ['edges' => [
        ['node' => [
            'name' => 'Beverley Crawford',
            'data' => [
                'fullName' => ['fieldValue' => 'Beverley Crawford'],
                'date' => ['fieldValue' => '1561-12-13'],
            ],
        ]],
    ]]]]);
});
