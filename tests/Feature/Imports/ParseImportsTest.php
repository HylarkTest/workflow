<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    fakeStorage('tmp');
});

test('a user can parse an import spreadsheet', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Cannot import', 'type' => FieldType::NUMBER(), 'options' => ['isRange' => true]],
        ['name' => 'Cannot import', 'type' => FieldType::MULTI(), 'options' => ['fields' => [
            ['name' => 'Profession', 'type' => FieldType::LINE()],
        ]]],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => [
                'fileId' => new Ignore,
                'headers' => ['Full name', 'Profession'],
                'rows' => [
                    ['row' => 0, 'data' => ['Full name', 'Profession']],
                    ['row' => 1, 'data' => ['Anakin Skywalker', 'Jedi']],
                    ['row' => 2, 'data' => ['Leia Organa', 'Princess']],
                    ['row' => 3, 'data' => ['Obi-wan Kenobi', 'Jedi']],
                    ['row' => 4, 'data' => ['Padmé Amidala', 'Senator']],
                    ['row' => 5, 'data' => ['Luke Skywalker', 'Jedi']],
                ],
                'data' => [
                    ['column' => 0, 'data' => ['Full name', 'Anakin Skywalker', 'Leia Organa', 'Obi-wan Kenobi', 'Padmé Amidala', 'Luke Skywalker']],
                    ['column' => 1, 'data' => ['Profession', 'Jedi', 'Princess', 'Jedi', 'Senator', 'Jedi']],
                ],
                'columnMapGuess' => [
                    ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                    ['column' => 1, 'fieldId' => $mapping->fields[2]->id().'.'.$mapping->fields[2]->fields()[0]->id()],
                ],
                'dateFormatGuess' => 'd/m/y',
            ],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
        ]]
    );
});

test('a user can retrieve previously parsed information', function () {
    $user = createUser();

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $tempId = $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => ['fileId' => new Ignore],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'file' => $file,
        ]]
    )->json('data.parseSpreadsheet.data.fileId');

    $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => [
                'fileId' => $tempId,
                'headers' => ['Full name', 'Profession'],
                'rows' => [
                    ['row' => 0, 'data' => ['Full name', 'Profession']],
                    ['row' => 1, 'data' => ['Anakin Skywalker', 'Jedi']],
                    ['row' => 2, 'data' => ['Leia Organa', 'Princess']],
                    ['row' => 3, 'data' => ['Obi-wan Kenobi', 'Jedi']],
                    ['row' => 4, 'data' => ['Padmé Amidala', 'Senator']],
                    ['row' => 5, 'data' => ['Luke Skywalker', 'Jedi']],
                ],
            ],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'fileId' => $tempId,
        ]]
    );
});

test('a user can parse a Microsoft contacts export', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
    ]]);

    $file = new UploadedFile(
        __DIR__.'/../../resources/imports/outlook_contacts_mix.csv',
        'contacts.csv',
        'text/csv',
        null,
        true
    );

    $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => [
                'fileId' => new Ignore,
                'headers' => ['First Name', 'Middle Name', 'Last Name', 'Title', 'Suffix', 'Nickname', 'Given Yomi', 'Surname Yomi', 'E-mail Address', 'E-mail 2 Address', 'E-mail 3 Address', 'Home Phone', 'Home Phone 2', 'Business Phone', 'Business Phone 2', 'Mobile Phone', 'Car Phone', 'Other Phone', 'Primary Phone', 'Pager', 'Business Fax', 'Home Fax', 'Other Fax', 'Company Main Phone', 'Callback', 'Radio Phone', 'Telex', 'TTY/TDD Phone', 'IMAddress', 'Job Title', 'Department', 'Company', 'Office Location', 'Manager\'s Name', 'Assistant\'s Name', 'Assistant\'s Phone', 'Company Yomi', 'Business Street', 'Business City', 'Business State', 'Business Postal Code', 'Business Country/Region', 'Home Street', 'Home City', 'Home State', 'Home Postal Code', 'Home Country/Region', 'Other Street', 'Other City', 'Other State', 'Other Postal Code', 'Other Country/Region', 'Personal Web Page', 'Spouse', 'Schools', 'Hobby', 'Location', 'Web Page', 'Birthday', 'Anniversary', 'Notes'],
                'rows' => [
                    ['row' => 0, 'data' => ['First Name', 'Middle Name', 'Last Name', 'Title', 'Suffix', 'Nickname', 'Given Yomi', 'Surname Yomi', 'E-mail Address', 'E-mail 2 Address', 'E-mail 3 Address', 'Home Phone', 'Home Phone 2', 'Business Phone', 'Business Phone 2', 'Mobile Phone', 'Car Phone', 'Other Phone', 'Primary Phone', 'Pager', 'Business Fax', 'Home Fax', 'Other Fax', 'Company Main Phone', 'Callback', 'Radio Phone', 'Telex', 'TTY/TDD Phone', 'IMAddress', 'Job Title', 'Department', 'Company', 'Office Location', 'Manager\'s Name', 'Assistant\'s Name', 'Assistant\'s Phone', 'Company Yomi', 'Business Street', 'Business City', 'Business State', 'Business Postal Code', 'Business Country/Region', 'Home Street', 'Home City', 'Home State', 'Home Postal Code', 'Home Country/Region', 'Other Street', 'Other City', 'Other State', 'Other Postal Code', 'Other Country/Region', 'Personal Web Page', 'Spouse', 'Schools', 'Hobby', 'Location', 'Web Page', 'Birthday', 'Anniversary', 'Notes']],
                    ['row' => 1, 'data' => ['Green', 'R.', 'Pepper', 'Dr.', 'Jr', 'Pepp', 'Gr-ee-n', 'Peppppppper', 'greenpepper@hylark.com', 'bestpepper@hylark.com', null, null, null, null, null, '0123123123123', null, null, null, null, null, null, null, null, null, null, null, null, 'CHAT???', null, null, 'Vegetables Inc.', null, null, null, null, null, 'Pepper Street', 'Pepper City', null, null, null, null, null, null, null, null, '17 Green Road', 'Green Town', null, null, 'UK', null, null, null, null, null, null, null, null, 'At still competition with Red Pepper']],
                    ['row' => 2, 'data' => ['Red Pepper', null, null, null, null, null, null, null, 'redpepper@hylark.com', null, null, null, null, '123123123123123', null, '123123123123123', '123123123123123123', null, null, null, null, null, null, null, null, null, null, null, null, 'Chief Vegetable Officer', 'Vegetable Department', 'Hylark', 'Vegetable City', null, null, null, 'Hylark', null, null, null, null, null, 'Pepper Street', 'Vegetable City', 'Surrey', 'GU7 1RP', 'UK', null, null, null, null, null, 'https://hylark.com', null, null, null, null, null, null, null, null]],
                    ['row' => 3, 'data' => ['Maria', null, null, null, null, null, null, null, 'maria@ezekia.com', 'maria@hylark.com', null, null, null, null, null, '+++++123', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'State / Province', null, null, null, null, null, null, null, null, null, null, 'This is me']],
                ],
                'columnMapGuess' => new NullFieldWithSubQuery('{ column fieldId }', true),
            ],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
        ]]
    );
});

test('a user can preview items from a spreadsheet', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Profession', 'type' => FieldType::LINE()],
        ['name' => 'Emails', 'type' => FieldType::EMAIL(), 'options' => ['list' => ['max' => 5]]],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people(first: 4)' => [
                'data' => [
                    [
                        'name' => 'Anakin Skywalker',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Anakin Skywalker'],
                            'profession' => ['fieldValue' => 'Jedi'],
                            'emails' => ['listValue' => [
                                ['fieldValue' => 'as@jedi.com'],
                                ['fieldValue' => 'dv@sith.com'],
                            ]],
                        ],
                    ],
                    [
                        'name' => 'Leia Organa',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Leia Organa'],
                            'profession' => ['fieldValue' => 'Princess'],
                            'emails' => ['listValue' => [
                                ['fieldValue' => 'lo@senate.com'],
                            ]],
                        ],
                    ],
                    [
                        'name' => 'Obi-wan Kenobi',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Obi-wan Kenobi'],
                            'profession' => ['fieldValue' => 'Jedi'],
                            'emails' => ['listValue' => [
                                ['fieldValue' => 'ok@jedi.com'],
                                ['fieldValue' => 'ben@tatooine.com'],
                            ]],
                        ],
                    ],
                    [
                        'name' => 'Padmé Amidala',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Padmé Amidala'],
                            'profession' => ['fieldValue' => 'Senator'],
                            'emails' => ['listValue' => [
                                ['fieldValue' => 'pa@senate.com'],
                            ]],
                        ],
                    ],
                ],
                'pageInfo' => [
                    //                    'count' => 5,
                    'hasMorePages' => true,
                    'currentPage' => 1,
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 1, 'fieldId' => $mapping->fields[1]->id()],
                ['column' => 2, 'fieldId' => $mapping->fields[2]->id()],
                ['column' => 3, 'fieldId' => $mapping->fields[2]->id()],
            ],
        ]],
    );

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people(first: 4, page: 2)' => [
                'data' => [
                    [
                        'name' => 'Luke Skywalker',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Luke Skywalker'],
                            'profession' => ['fieldValue' => 'Jedi'],
                            'emails' => ['listValue' => [
                                ['fieldValue' => 'ls@rebels.com'],
                                ['fieldValue' => 'ls@jedi.com'],
                            ]],
                        ],
                    ],
                ],
                'pageInfo' => [
                    //                    'count' => 5,
                    'hasMorePages' => false,
                    'currentPage' => 2,
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 1, 'fieldId' => $mapping->fields[1]->id()],
                ['column' => 2, 'fieldId' => $mapping->fields[2]->id()],
                ['column' => 3, 'fieldId' => $mapping->fields[2]->id()],
            ],
        ]],
    );
});

test('a user can see validation issues in a preview', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Birthday', 'type' => FieldType::DATE()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', <<<'CSV'
        Full name,Birthday
        Anakin Skywalker,13/01/2003
        Leia Organa,03/01/2003
    CSV);

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Anakin Skywalker',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Anakin Skywalker'],
                            'birthday' => new NullFieldWithSubQuery('{ fieldValue }'),
                        ],
                        'errors' => [[
                            'row' => 2,
                            'column' => 1,
                            'fieldId' => $mapping->fields[1]->id(),
                            'errors' => ['The value is not a valid date.'],
                        ]],
                    ],
                    [
                        'name' => 'Leia Organa',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Leia Organa'],
                            'birthday' => ['fieldValue' => '2003-03-01'],
                        ],
                        'errors' => [],
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 1, 'fieldId' => $mapping->fields[1]->id()],
            ],
        ]],
    );
});

test('a user can see rows that would fail to import in a preview', function () {
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

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Leia Organa',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Leia Organa'],
                            'profession' => ['fieldValue' => 'Princess'],
                        ],
                    ],
                    null,
                ],
                'errors' => [
                    [
                        'row' => 3,
                        'path' => ['items', 'people', 'previewPeople', 'people', 'data', '1'],
                        'error' => 'Name field cannot be empty.',
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 1, 'fieldId' => $mapping->fields[1]->id()],
            ],
        ]],
    );
});

test('Excel spreadsheets with dates can be imported', function () {
    $user = createUser();
    $mapping = createMapping($user, ['fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Birthday', 'type' => FieldType::DATE()],
    ]]);

    $file = new UploadedFile(
        __DIR__.'/../../resources/imports/dummy_imports.xlsx',
        'dummy_imports.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => [
                'fileId' => new Ignore,
                'headers' => ['Full name', 'Preferred name', 'Phone number', 'Address', 'Birthday', 'Email'],
                'rows' => [
                    ['row' => 0, 'data' => ['Full name', 'Preferred name', 'Phone number', 'Address', 'Birthday', 'Email']],
                    ['row' => 1, 'data' => ['Darlene Burton', 'Darlene', '(272) 790-0888', '9202 Thornridge Cir', '28887', 'darlene.burton@example.com']],
                    ['row' => 2, 'data' => ['Jennie Nichols', 'Jennie', '(2489) 330-2385', '8929 Valwood Pkwy', '33671', 'jennie.nichols@example.com']],
                    ['row' => 3, 'data' => ['Efe Abadan', 'Ef', '(659)-446-3603', '5229 Kushimoto Sk', '36551', 'efe.abadan@example.com']],
                ],
            ],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
        ]]
    );
});

test('a user can specify the date format for a preview', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Birthday', 'type' => FieldType::DATE()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', testCSV());

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people(first: 2)' => [
                'data' => [
                    [
                        'name' => 'Anakin Skywalker',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Anakin Skywalker'],
                            'birthday' => ['fieldValue' => '2041-09-03'],
                        ],
                    ],
                    [
                        'name' => 'Leia Organa',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Leia Organa'],
                            'birthday' => ['fieldValue' => '2019-01-01'],
                        ],
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 4, 'fieldId' => $mapping->fields[1]->id()],
            ],
            'dateFormat' => 'd/m/y',
        ]],
    );
});

test('empty columns are removed from the parser but column indexes are preserved', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Profession', 'type' => FieldType::LINE()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', <<<'CSV'
Full name,,Profession,,
Anakin Skywalker,,Jedi,,
Leia Organa,,Princess,,
CSV);

    $this->be($user)->assertGraphQLMutation(
        ['parseSpreadsheet(input: $input)' => [
            'code' => '200',
            'data' => [
                'fileId' => new Ignore,
                'headers' => ['Full name', 'Profession'],
                'rows' => [
                    ['row' => 0, 'data' => ['Full name', 'Profession']],
                    ['row' => 1, 'data' => ['Anakin Skywalker', 'Jedi']],
                    ['row' => 2, 'data' => ['Leia Organa', 'Princess']],
                ],
                'data' => [
                    ['column' => 0, 'data' => ['Full name', 'Anakin Skywalker', 'Leia Organa']],
                    ['column' => 2, 'data' => ['Profession', 'Jedi', 'Princess']],
                ],
                'columnMapGuess' => [
                    ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                    ['column' => 2, 'fieldId' => $mapping->fields[1]->id()],
                ],
            ],
        ]],
        ['input: ParseSpreadsheetInput!' => [
            'file' => $file,
            'mappingId' => $mapping->global_id,
        ]]
    );

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Anakin Skywalker',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Anakin Skywalker'],
                            'profession' => ['fieldValue' => 'Jedi'],
                        ],
                    ],
                    [
                        'name' => 'Leia Organa',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Leia Organa'],
                            'profession' => ['fieldValue' => 'Princess'],
                        ],
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => [
                ['column' => 0, 'fieldId' => $mapping->fields[0]->id()],
                ['column' => 2, 'fieldId' => $mapping->fields[1]->id()],
            ],
        ]],
    );
});

test('all importable fields can be imported', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Boolean', 'type' => FieldType::BOOLEAN()],
        ['name' => 'Currency', 'type' => FieldType::CURRENCY()],
        ['name' => 'Date', 'type' => FieldType::DATE()],
        ['name' => 'Date time', 'type' => FieldType::DATE_TIME()],
        ['name' => 'Time', 'type' => FieldType::TIME()],
        ['name' => 'Email', 'type' => FieldType::EMAIL()],
        ['name' => 'Number', 'type' => FieldType::NUMBER()],
        ['name' => 'Line', 'type' => FieldType::LINE()],
        ['name' => 'Name', 'type' => FieldType::NAME()],
        ['name' => 'Paragraph', 'type' => FieldType::PARAGRAPH()],
        ['name' => 'Phone', 'type' => FieldType::PHONE()],
        ['name' => 'Rating', 'type' => FieldType::RATING()],
        ['name' => 'Url', 'type' => FieldType::URL()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', <<<'CSV'
Full name,Boolean,Currency,Date,Date time,Time,Email,Number,Line,Name,Paragraph,Phone,Rating,Url
Linda,1,USD,13/01/2003,13/01/2003 13:00:00,13:00,linda@example.com,1234,Line,Name,Paragraph,123456789,5,https://example.com
CSV);

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Linda',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Linda'],
                            'boolean' => ['fieldValue' => true],
                            'currency' => ['fieldValue' => 'USD'],
                            'date' => ['fieldValue' => '2003-01-13'],
                            'dateTime' => ['fieldValue' => '2003-01-13 13:00:00'],
                            'time' => ['fieldValue' => '13:00'],
                            'email' => ['fieldValue' => 'linda@example.com'],
                            'number' => ['fieldValue' => 1234],
                            'line' => ['fieldValue' => 'Line'],
                            'name' => ['fieldValue' => 'Name'],
                            'paragraph' => ['fieldValue' => 'Paragraph'],
                            'phone' => ['fieldValue' => '123456789'],
                            'rating' => ['fieldValue' => ['stars' => 5, 'max' => 5]],
                            'url' => ['fieldValue' => 'https://example.com'],
                        ],
                        'errors' => new NullFieldWithSubQuery('{ row column fieldId errors }', true),
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => $mapping->fields->map(fn (Field $field, $column) => ['column' => $column, 'fieldId' => $field->id()])->all(),
            'dateFormat' => 'd/m/y',
        ]],
    );
});

test('all fields are validated', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Boolean', 'type' => FieldType::BOOLEAN()],
        ['name' => 'Currency', 'type' => FieldType::CURRENCY()],
        ['name' => 'Date', 'type' => FieldType::DATE()],
        ['name' => 'Date time', 'type' => FieldType::DATE_TIME()],
        ['name' => 'Time', 'type' => FieldType::TIME()],
        ['name' => 'Email', 'type' => FieldType::EMAIL()],
        ['name' => 'Number', 'type' => FieldType::NUMBER()],
        ['name' => 'Line', 'type' => FieldType::LINE()],
        ['name' => 'Name', 'type' => FieldType::NAME()],
        ['name' => 'Paragraph', 'type' => FieldType::PARAGRAPH()],
        ['name' => 'Phone', 'type' => FieldType::PHONE()],
        ['name' => 'Rating', 'type' => FieldType::RATING()],
        ['name' => 'Url', 'type' => FieldType::URL()],
    ]]);

    $file = UploadedFile::fake()->create('import.csv', <<<'CSV'
Full name,Boolean,Currency,Date,Date time,Time,Email,Number,Line,Name,Paragraph,Phone,Rating,Url
Linda,not boolean,not currency,not date,not date time,not time,not email,not number,,,,,10,
CSV);

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Linda',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Linda'],
                            'boolean' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'currency' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'date' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'dateTime' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'time' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'email' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'number' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'line' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'name' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'paragraph' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'phone' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'rating' => new NullFieldWithSubQuery('{ fieldValue { stars max } }'),
                            'url' => new NullFieldWithSubQuery('{ fieldValue }'),
                        ],
                        'errors' => [
                            ['row' => 2, 'column' => 1, 'fieldId' => $mapping->fields[1]->id(), 'errors' => ['The value field must be true or false.']],
                            ['row' => 2, 'column' => 2, 'fieldId' => $mapping->fields[2]->id(), 'errors' => ['The selected value is invalid.']],
                            ['row' => 2, 'column' => 3, 'fieldId' => $mapping->fields[3]->id(), 'errors' => ['The value is not a valid date.']],
                            ['row' => 2, 'column' => 4, 'fieldId' => $mapping->fields[4]->id(), 'errors' => ['The value is not a valid date.']],
                            ['row' => 2, 'column' => 5, 'fieldId' => $mapping->fields[5]->id(), 'errors' => ['The value must be a valid time.']],
                            ['row' => 2, 'column' => 6, 'fieldId' => $mapping->fields[6]->id(), 'errors' => ['The value must be a valid email address.']],
                            ['row' => 2, 'column' => 7, 'fieldId' => $mapping->fields[7]->id(), 'errors' => ['The value must be a number.']],
                            ['row' => 2, 'column' => 12, 'fieldId' => $mapping->fields[12]->id(), 'errors' => ['The value must not be greater than 5.']],
                        ],
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => $mapping->fields->map(fn (Field $field, $column) => ['column' => $column, 'fieldId' => $field->id()])->all(),
            'dateFormat' => 'd/m/y',
        ]],
    );
});

test('all importable fields can be imported from excel', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People', 'fields' => [
        ['name' => 'Full name', 'type' => FieldType::SYSTEM_NAME()],
        ['name' => 'Boolean', 'type' => FieldType::BOOLEAN()],
        ['name' => 'Currency', 'type' => FieldType::CURRENCY()],
        ['name' => 'Date', 'type' => FieldType::DATE()],
        ['name' => 'Date time', 'type' => FieldType::DATE_TIME()],
        ['name' => 'Time', 'type' => FieldType::TIME()],
        ['name' => 'Email', 'type' => FieldType::EMAIL()],
        ['name' => 'Number', 'type' => FieldType::NUMBER()],
        ['name' => 'Line', 'type' => FieldType::LINE()],
        ['name' => 'Name', 'type' => FieldType::NAME()],
        ['name' => 'Paragraph', 'type' => FieldType::PARAGRAPH()],
        ['name' => 'Phone', 'type' => FieldType::PHONE()],
        ['name' => 'Rating', 'type' => FieldType::RATING()],
        ['name' => 'Url', 'type' => FieldType::URL()],
    ]]);

    $file = new UploadedFile(
        __DIR__.'/../../resources/imports/all_fields_import.xlsx',
        'contacts.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Linda',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Linda'],
                            'boolean' => ['fieldValue' => true],
                            'currency' => ['fieldValue' => 'USD'],
                            'date' => ['fieldValue' => '2003-01-13'],
                            'dateTime' => ['fieldValue' => '2003-01-13 13:00:00'],
                            'time' => ['fieldValue' => '13:00:00'],
                            'email' => ['fieldValue' => 'linda@example.com'],
                            'number' => ['fieldValue' => 1234],
                            'line' => ['fieldValue' => 'Line'],
                            'name' => ['fieldValue' => 'Name'],
                            'paragraph' => ['fieldValue' => 'Paragraph'],
                            'phone' => ['fieldValue' => '123456789'],
                            'rating' => ['fieldValue' => ['stars' => 5, 'max' => 5]],
                            'url' => ['fieldValue' => 'https://example.com'],
                        ],
                        'errors' => new NullFieldWithSubQuery('{ row column fieldId errors }', true),
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => $mapping->fields->map(fn (Field $field, $column) => ['column' => $column, 'fieldId' => $field->id()])->all(),
            'dateFormat' => 'd/m/y',
        ]],
    );
});

test('really old dates can be parsed from excel', function () {
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
        ['items' => ['people' => ['previewPeople(input: $input)' => [
            'code' => '200',
            'people' => [
                'data' => [
                    [
                        'name' => 'Beverley Crawford',
                        'data' => [
                            'fullName' => ['fieldValue' => 'Beverley Crawford'],
                            'date' => ['fieldValue' => '1561-12-13'],
                        ],
                    ],
                ],
            ],
        ]]]],
        ['input: PreviewSpreadsheetInput!' => [
            'file' => $file,
            'columnMap' => $mapping->fields->map(fn (Field $field, $column) => ['column' => $column, 'fieldId' => $field->id()])->all(),
            'dateFormat' => 'd/m/y',
        ]],
    );
});
