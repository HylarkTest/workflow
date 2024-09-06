<?php

declare(strict_types=1);

use App\Core\BaseType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Fields\Types\CategoryField;

uses(RefreshDatabase::class);

test('an empty request creates an account', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'baseType' => 'PERSONAL',
        'name' => 'My base',
    ]])->assertSuccessful();

    $base = $user->firstPersonalBase();
    expect($base)->name->toBe('My base')
        ->spaces->toHaveCount(1)
        ->and($base->spaces->first())->name->toBe('Personal')
        ->and($base->pivot)->use_account_avatar->toBeTrue();
});

test('names and types are required', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'markerGroups' => [[]],
        'categories' => [[]],
        'spaces' => [[
            'pages' => [[
                'fields' => [[]],
                'relationships' => [[]],
            ]],
        ]],
    ]])->assertJsonValidationErrors([
        '0.markerGroups.0.name' => 'The tag group name field is required.',
        '0.categories.0.name' => 'The category name field is required.',
        '0.spaces.0.name' => 'The space name field is required.',
        '0.spaces.0.pages.0.fields.0.name' => 'The field name field is required.',
        '0.spaces.0.pages.0.fields.0.type' => 'The field type field is required.',
        '0.spaces.0.pages.0.relationships.0.type' => 'The relationship type field is required.',
    ]);

    expect($user->firstPersonalBase()->mappings)->toBeEmpty();
});

test('strings cannot be too long', function () {
    $longString = str_pad('', 256, 'a');
    $longerString = str_pad('', 1024, 'a');

    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => $longString,
        'markerGroups' => [[
            'name' => $longString,
            'description' => $longerString,
        ]],
        'categories' => [[
            'name' => $longString,
            'description' => $longerString,
        ]],
        'spaces' => [[
            'name' => $longString,
            'description' => $longerString,
            'pages' => [[
                'name' => $longString,
                'description' => $longerString,
                'fields' => [['name' => $longString]],
                'relationships' => [['name' => $longString]],
            ]],
        ]],
    ]])->assertJsonValidationErrors([
        '0.name' => 'The base name must not be greater',
        '0.markerGroups.0.name' => 'The tag group name must not be greater',
        '0.markerGroups.0.description' => 'The tag group description must not be greater',
        '0.categories.0.name' => 'The category name must not be greater',
        '0.categories.0.description' => 'The category description must not be greater',
        '0.spaces.0.name' => 'The space name must not be greater',
        '0.spaces.0.description' => 'The space description must not be greater',
        '0.spaces.0.pages.0.name' => 'The page name must not be greater',
        '0.spaces.0.pages.0.description' => 'The page description must not be greater',
        '0.spaces.0.pages.0.fields.0.name' => 'The field name must not be greater',
        '0.spaces.0.pages.0.relationships.0.name' => 'The relationship name must not be greater',
    ]);

    expect($user->firstPersonalBase()->mappings)->toBeEmpty();
});

test('names and ids must be distinct within groups but not outside', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => 'Personal',
        'markerGroups' => [
            ['id' => '123', 'name' => 'Status'],
            ['id' => '123', 'name' => 'Status'],
        ],
        'categories' => [
            ['id' => '123', 'name' => 'Industries'],
            ['id' => '123', 'name' => 'Industries'],
        ],
        'spaces' => [
            [
                'name' => 'Personal',
                'pages' => [
                    [
                        'name' => 'Hobbies',
                        'fields' => [
                            ['name' => 'Name'],
                            ['name' => 'Name'],
                        ],
                        'relationships' => [
                            ['name' => 'related'],
                            ['name' => 'related'],
                        ],
                    ],
                    [
                        'name' => 'Hobbies',
                        'fields' => [
                            ['name' => 'Name'],
                        ],
                        'relationships' => [
                            ['name' => 'related'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Personal',
                'pages' => [[
                    'name' => 'Hobbies',
                    'fields' => [['name' => 'Name']],
                    'relationships' => [['name' => 'related']],
                ]],
            ],
        ],
    ]])->assertJsonValidationErrors([
        '0.markerGroups.0.name' => 'The tag group name field has a duplicate value',
        '0.markerGroups.1.name' => 'The tag group name field has a duplicate value',
        '0.markerGroups.0.id' => 'The tag group ID field has a duplicate value',
        '0.markerGroups.1.id' => 'The tag group ID field has a duplicate value',
        '0.categories.0.name' => 'The category name field has a duplicate value',
        '0.categories.1.name' => 'The category name field has a duplicate value',
        '0.categories.0.id' => 'The category ID field has a duplicate value',
        '0.categories.1.id' => 'The category ID field has a duplicate value',
        '0.spaces.0.name' => 'The space name field has a duplicate value',
        '0.spaces.1.name' => 'The space name field has a duplicate value',
        '0.spaces.0.pages.0.fields.0.name' => 'The field name field has a duplicate value',
        '0.spaces.0.pages.0.fields.1.name' => 'The field name field has a duplicate value',
        '0.spaces.0.pages.0.relationships.0.name' => 'The relationship name field has a duplicate value',
        '0.spaces.0.pages.0.relationships.1.name' => 'The relationship name field has a duplicate value',
    ])->assertValid([
        '0.spaces.1.pages.0.name',
        '0.spaces.0.pages.1.fields.0.name',
        '0.spaces.1.pages.0.fields.0.name',
        '0.spaces.0.pages.1.relationships.0.name',
        '0.spaces.1.pages.0.relationships.0.name',
    ]);

    expect($user->firstPersonalBase()->mappings)->toBeEmpty();
});

test('field options are validated', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => 'Personal',
        'spaces' => [[
            'name' => 'A space',
            'pages' => [[
                'name' => 'Pages',
                'type' => 'ENTITIES',
                'fields' => [
                    [
                        'name' => 'Category',
                        'type' => 'CATEGORY',
                        'options' => ['category' => null],
                    ],
                ],
            ]],
        ]],
    ]])->assertJsonValidationErrors([
        '0.spaces.0.pages.0.fields' => 'At least one field of type SYSTEM_NAME is required',
        '0.spaces.0.pages.0.fields.0.options.category' => 'The category field is required',
    ]);
});

test('an account can be bootstrapped without a use', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => 'Test 1',
    ]]);

    $base = $user->firstPersonalBase();

    expect($base)->name->toBe('Test 1')
        ->spaces->toHaveCount(1)
        ->and($base->spaces->first())->name->toBe('Personal');
});

test('a collaborative base account can be bootstrapped without a use', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => 'Test 1',
        'baseType' => 'COLLABORATIVE',
    ]]);
    $user->unsetRelations();

    expect($user->bases)->toHaveCount(2);

    $base = $user->firstPersonalBase();

    expect($base)->name->toBe('My base')
        ->spaces->toHaveCount(1)
        ->and($base->spaces->first())->name->toBe('Personal');

    $collaborativeBase = $user->bases->last();

    expect($collaborativeBase)->name->toBe('Test 1')
        ->spaces->toHaveCount(1)
        ->and($collaborativeBase->spaces->first())->name->toBe('Main')
        ->and($collaborativeBase->todoLists)->toHaveCount(1);
});

test('an account can be successfully bootstrapped', function () {
    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'name' => 'Personal',
        'markerGroups' => [
            [
                'id' => 'CAREER_CONTACT_DESCRIPTOR_TAG_TEMP',
                'name' => 'Descriptor',
                'type' => 'TAG',
                'markers' => [
                    ['name' => 'Acquaintance', 'color' => '#012a4a'],
                    ['name' => 'Friend', 'color' => '#013a63'],
                    ['name' => 'Family', 'color' => '#01497c'],
                    ['name' => 'Good Source', 'color' => '#014f86'],
                    ['name' => 'Work', 'color' => '#2a6f97'],
                ],
            ],
            [
                'id' => 'CAREER_CONTACT_PIPELINE_TAG_TEMP',
                'name' => 'Contact pipeline',
                'type' => 'PIPELINE',
                'markers' => [
                    ['name' => 'Contacted', 'color' => '#081c15'],
                    ['name' => 'Awaiting Reply', 'color' => '#1b4332'],
                ],
            ],
            [
                'id' => 'CAREER_PIPELINE_TAG_TEMP',
                'name' => 'Career pipeline',
                'type' => 'PIPELINE',
                'markers' => [
                    ['name' => 'Identified', 'color' => '#277da1'],
                    ['name' => 'Applying', 'color' => '#4d908e'],
                    ['name' => 'Waiting for reply', 'color' => '#72195a'],
                    ['name' => 'Interview', 'color' => '#43aa8b'],
                    ['name' => 'Closed', 'color' => '#577590'],
                    ['name' => 'Job offer', 'color' => '#222823'],
                ],
            ],
        ],
        'categories' => [
            [
                'id' => 'INDUSTRIES_TEMP',
                'name' => 'Industries',
                'items' => [
                    ['name' => 'Business'],
                    ['name' => 'Social'],
                    ['name' => 'Engineering'],
                ],
            ],
        ],
        'spaces' => [
            [
                'name' => 'Career',
                'pages' => [
                    [
                        'id' => 'POSITIONS',
                        'templateRefs' => ['POSITIONS'],
                        'name' => 'Positions',
                        'pageType' => 'ENTITIES',
                        'singularName' => 'Position',
                        'folder' => 'Job search',
                        'fields' => [
                            ['name' => 'Name', 'type' => 'SYSTEM_NAME'],
                            ['name' => 'Description', 'type' => 'FORMATTED'],
                            ['name' => 'Location', 'type' => 'LOCATION'],
                            [
                                'name' => 'Commute duration',
                                'type' => 'SELECT',
                                'options' => [
                                    'valueOptions' => [
                                        1 => 'None',
                                        2 => 'Under 30 minutes',
                                        3 => '30 - 60 minutes',
                                        4 => '1 - 2 hours',
                                        5 => '2+ hours',
                                    ],
                                ],
                            ],
                            ['name' => 'Interest', 'type' => 'RATING'],
                            [
                                'name' => 'Links',
                                'type' => 'URL',
                                'options' => [
                                    'list' => true,
                                    'labeled' => ['freetext' => true],
                                ],
                            ],
                            ['name' => 'Advantages', 'type' => 'FORMATTED'],
                            ['name' => 'Disadvantages', 'type' => 'FORMATTED'],
                            [
                                'name' => 'Workplace',
                                'type' => 'SELECT',
                                'options' => [
                                    'valueOptions' => [
                                        1 => 'Hybrid',
                                        2 => 'Remote',
                                        3 => 'In person',
                                    ],
                                ],
                                'meta' => ['displayStyle' => 'RADIO'],
                            ],
                            ['name' => 'Job title', 'type' => 'LINE'],
                            [
                                'name' => 'Salary range',
                                'type' => 'MONEY',
                                'options' => ['isRange' => true],
                            ],
                            [
                                'name' => 'Industry',
                                'type' => 'CATEGORY',
                                'options' => ['category' => 'INDUSTRIES_TEMP'],
                            ],
                            [
                                'name' => 'Position type',
                                'type' => 'SELECT',
                                'options' => [
                                    'valueOptions' => [
                                        1 => 'Permanent',
                                        2 => 'Temporary',
                                        3 => 'Contract',
                                        4 => 'Part time',
                                    ],
                                ],
                                'meta' => ['displayStyle' => 'DROPDOWN_SM'],
                            ],
                            [
                                'name' => 'Organization type',
                                'type' => 'SELECT',
                                'options' => [
                                    'valueOptions' => [
                                        1 => 'Charity',
                                        2 => 'Enterprise',
                                        3 => 'Large',
                                        4 => 'Medium',
                                        5 => 'Micro',
                                        6 => 'Not for profit',
                                        7 => 'PE backed',
                                        8 => 'Private',
                                        9 => 'Public',
                                        10 => 'Public sector',
                                        11 => 'Small',
                                        12 => 'Startup',
                                    ],
                                ],
                                'meta' => ['displayStyle' => 'DROPDOWN_SM'],
                            ],
                        ],
                        'features' => [
                            ['val' => 'TIMEKEEPER'],
                        ],
                        'markerGroups' => [
                            'CAREER_PIPELINE_TAG_TEMP',
                        ],
                    ],
                    [
                        'id' => 'CONTACTS',
                        'name' => 'People',
                        'singularName' => 'Person',
                        'pageType' => 'ENTITIES',
                        'fields' => [
                            ['name' => 'Title', 'type' => 'TITLE'],
                            ['name' => 'Full name', 'type' => 'SYSTEM_NAME'],
                            ['name' => 'Preferred name', 'type' => 'NAME'],
                            [
                                'name' => 'emails',
                                'type' => 'EMAIL',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Personal', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'phones',
                                'type' => 'PHONE',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Mobile', 'Home', 'Work', 'School', 'Switchboard', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'addresses',
                                'type' => 'ADDRESS',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Home', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'image',
                                'type' => 'IMAGE',
                                'options' => ['croppable' => true],
                            ],
                            [
                                'name' => 'positions',
                                'type' => 'MULTI',
                                'options' => [
                                    'list' => true,
                                    'fields' => [
                                        ['name' => 'Job title', 'type' => 'LINE'],
                                        ['name' => 'Organization', 'type' => 'LINE'],
                                        ['name' => 'Department', 'type' => 'LINE'],
                                        [
                                            'name' => 'Position type',
                                            'type' => 'SELECT',
                                            'options' => [
                                                'valueOptions' => [
                                                    1 => 'Permanent',
                                                    2 => 'Temporary',
                                                    3 => 'Contract',
                                                    4 => 'Part time',
                                                ],
                                            ],
                                            'meta' => ['displayStyle' => 'DROPDOWN_SM'],
                                        ],
                                        ['name' => 'Start date', 'type' => 'DATE', 'options' => ['precision' => 'Y-m-d']],
                                        ['name' => 'End date', 'type' => 'DATE', 'options' => ['precision' => 'Y-m-d', 'allowPresent' => true]],
                                        ['name' => 'Location', 'type' => 'LOCATION'],
                                        ['name' => 'Additional', 'type' => 'PARAGRAPH'],
                                        ['name' => 'Industry', 'type' => 'CATEGORY', 'options' => ['category' => 'INDUSTRIES_TEMP']],
                                    ],
                                ],
                            ],
                        ],
                        'features' => [
                            ['val' => 'EMAILS'],
                        ],
                        'markerGroups' => [
                            'CAREER_CONTACT_DESCRIPTOR_TAG_TEMP',
                            'CAREER_CONTACT_PIPELINE_TAG_TEMP',
                        ],
                    ],
                    [
                        'id' => 'COMPANIES',
                        'name' => 'Organizations',
                        'singularName' => 'Organization',
                        'folder' => 'Job search',
                        'pageType' => 'ENTITIES',
                        'fields' => [
                            ['name' => 'Name', 'type' => 'SYSTEM_NAME'],
                            ['name' => 'Description', 'type' => 'FORMATTED'],
                            ['name' => 'Industry', 'type' => 'CATEGORY', 'options' => ['category' => 'INDUSTRIES_TEMP']],
                            [
                                'name' => 'emails',
                                'type' => 'EMAIL',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Personal', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'phones',
                                'type' => 'PHONE',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Mobile', 'Home', 'Work', 'School', 'Switchboard', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'addresses',
                                'type' => 'ADDRESS',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Home', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Organization type',
                                'type' => 'SELECT',
                                'options' => [
                                    'valueOptions' => [
                                        1 => 'Charity',
                                        2 => 'Enterprise',
                                        3 => 'Large',
                                        4 => 'Medium',
                                        5 => 'Micro',
                                        6 => 'Not for profit',
                                        7 => 'PE backed',
                                        8 => 'Private',
                                        9 => 'Public',
                                        10 => 'Public sector',
                                        11 => 'Small',
                                        12 => 'Startup',
                                    ],
                                ],
                                'meta' => ['displayStyle' => 'DROPDOWN_SM'],
                            ],
                        ],
                        'relationships' => [
                            ['name' => 'Positions', 'inverse' => 'Organization', 'type' => 'ONE_TO_MANY', 'to' => 'POSITIONS'],
                            ['name' => 'Employees', 'inverse' => 'Organizations', 'type' => 'MANY_TO_MANY', 'to' => 'CONTACTS'],
                        ],
                    ],
                    [
                        'id' => 'CONTACTS',
                        'name' => 'Leads',
                        'singularName' => 'Lead',
                        'pageType' => 'ENTITIES',
                        'fields' => [
                            ['name' => 'Title', 'type' => 'TITLE'],
                            ['name' => 'Full name', 'type' => 'SYSTEM_NAME'],
                            ['name' => 'Preferred name', 'type' => 'NAME'],
                            [
                                'name' => 'emails',
                                'type' => 'EMAIL',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Personal', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'phones',
                                'type' => 'PHONE',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Mobile', 'Home', 'Work', 'School', 'Switchboard', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'addresses',
                                'type' => 'ADDRESS',
                                'options' => [
                                    'list' => true,
                                    'labelled' => [
                                        'labels' => ['Home', 'Work', 'School', 'Other'],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'image',
                                'type' => 'IMAGE',
                                'options' => ['croppable' => true],
                            ],
                            [
                                'name' => 'positions',
                                'type' => 'MULTI',
                                'options' => [
                                    'list' => true,
                                    'fields' => [
                                        ['name' => 'Job title', 'type' => 'LINE'],
                                        ['name' => 'Organization', 'type' => 'LINE'],
                                        ['name' => 'Department', 'type' => 'LINE'],
                                        [
                                            'name' => 'Position type',
                                            'type' => 'SELECT',
                                            'options' => [
                                                'valueOptions' => [
                                                    1 => 'Permanent',
                                                    2 => 'Temporary',
                                                    3 => 'Contract',
                                                    4 => 'Part time',
                                                ],
                                            ],
                                            'meta' => ['displayStyle' => 'DROPDOWN_SM'],
                                        ],
                                        ['name' => 'Start date', 'type' => 'DATE', 'options' => ['precision' => 'Y-m-d']],
                                        ['name' => 'End date', 'type' => 'DATE', 'options' => ['precision' => 'Y-m-d', 'allowPresent' => true]],
                                        ['name' => 'Location', 'type' => 'LOCATION'],
                                        ['name' => 'Additional', 'type' => 'PARAGRAPH'],
                                        ['name' => 'Industry', 'type' => 'CATEGORY', 'options' => ['category' => 'INDUSTRIES_TEMP']],
                                    ],
                                ],
                            ],
                        ],
                        'features' => [
                            ['val' => 'EMAILS'],
                        ],
                        'markerGroups' => [
                            'CAREER_CONTACT_DESCRIPTOR_TAG_TEMP',
                        ],
                    ],
                ],
                'features' => [
                    [
                        'val' => 'NOTES',
                        'relatesTo' => '*',
                        'options' => [
                            'formatted' => true,
                        ],
                    ],
                    ['val' => 'TODOS', 'relatesTo' => '*'],
                    ['val' => 'CALENDAR', 'relatesTo' => '*'],
                    ['val' => 'DOCUMENTS', 'relatesTo' => '*'],
                ],
            ],
        ],
    ]])->assertSuccessful();

    /** @var \App\Models\User $user */
    $user = $user->fresh();
    $base = $user->firstPersonalBase();

    expect($base->spaces)->toHaveCount(1);
    /** @var \App\Models\Space $space */
    $space = $base->spaces->first();
    expect($space->pages)->toHaveCount(4)
        ->and($space->mappings)->toHaveCount(3)
        ->and($space->pages->first()->path)->toBe('Job search/Positions');

    [$positions, $contacts, $organizations, $otherContacts] = $space->pages->pluck('mapping');
    expect($contacts->is($otherContacts))->toBeTrue()
        ->and($positions->fields)->toHaveCount(14)
        ->and($positions->templateRefs)->toBe(['POSITIONS'])
        ->and($positions->pages->first()->template_refs)->toBe(['POSITIONS'])
        ->and(FieldType::SYSTEM_NAME()->is($positions->fields->first()->type()))->toBeTrue()
        ->and($contacts->fields)->toHaveCount(8)
        ->and(FieldType::SYSTEM_NAME()->is($contacts->fields->get(1)->type()))->toBeTrue()
        ->and($organizations->fields)->toHaveCount(7)
        ->and(FieldType::SYSTEM_NAME()->is($organizations->fields->first()->type()))->toBeTrue()
        ->and($base->markerGroups)->toHaveCount(4)
        ->and($base->categories)->toHaveCount(1);

    $industries = $base->categories->first();
    expect($positions->fields->whereInstanceOf(CategoryField::class)->first()->category()->is($industries))->toBeTrue();
    [$general, $contactDescriptor, $contactPipeline, $careerPipeline] = $base->markerGroups;
    expect($contactDescriptor->id)->toBe($contacts->markerGroups->get(0)->group)
        ->and($contacts->markerGroups)->toHaveCount(2)
        ->and($contactPipeline->id)->toBe($contacts->markerGroups->get(1)->group)
        ->and($careerPipeline->id)->toBe($positions->markerGroups->get(0)->group);
});

test('an example bootstrap request works', function () {
    $body = json_decode('[{"id":1,"baseType":"PERSONAL","spaces":[{"id":1,"name":"Space 1","contributors":[{"val":"EXECUTIVE_CAREER"},{"val":"CALENDAR"}],"pages":[{"name":"Career contacts","singularName":"Career contact","pageName":"Career network","symbol":"fa-address-book","pageType":"ENTITIES","id":"CAREER_CONTACT","description":"A centralized space to manage contacts related to your career, including colleagues, managers, and recruiters, and for professional networking.","fields":[{"type":"SYSTEM_NAME","id":"SYSTEM_NAME","nameKey":"FULL_NAME","name":"Full name"},{"type":"NAME","options":{"type":"PREFERRED_NAME","list":false,"labeled":false},"id":"PREFERRED_NAME","name":"Preferred name"},{"type":"IMAGE","options":{"croppable":true,"primary":true,"list":false,"labeled":false},"id":"IMAGE","exampleKey":"IMAGE","name":"Image"},{"type":"ADDRESS","options":{"list":true,"labeled":{"labels":{"1":"Home","2":"Work","3":"School","4":"Other"}}},"id":"ADDRESSES","name":"Addresses"},{"type":"PHONE","options":{"list":true,"labeled":{"labels":{"1":"Mobile","2":"Personal","3":"Work","4":"School","5":"Other"}}},"id":"PHONES","exampleKey":"PHONE","name":"Phone numbers"},{"type":"EMAIL","options":{"list":true,"labeled":{"labels":{"1":"Personal","2":"Work","3":"School","4":"Other"}}},"id":"EMAILS","exampleKey":"EMAIL","name":"Emails"},{"type":"URL","options":{"list":true,"labeled":{"freeText":true}},"id":"LINKS","nameKey":"LINKS","exampleKey":"LINK","name":"Links"},{"type":"MULTI","options":{"fields":[{"type":"LINE","options":{"list":false,"labeled":false},"id":"ROLE","name":"Role"},{"type":"LINE","options":{"list":false,"labeled":false},"id":"ORGANIZATION_NAME","name":"Organization name"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"DESCRIPTION","name":"Description"},{"type":"BOOLEAN","meta":{"display":"TOGGLE"},"options":{"list":false,"labeled":false},"id":"IS_CURRENT","name":"Is current"},{"type":"SELECT","meta":{"display":"DROPDOWN"},"options":{"valueOptions":{"1":"Full-time","2":"Part-time","3":"Contract","4":"Freelance"},"multiSelect":false,"list":false,"labeled":false},"id":"POSITION_TYPE","name":"Position type"}],"list":true,"labeled":false},"id":"BASIC_POSITIONS","nameKey":"POSITION","name":"Position"}],"features":[{"val":"EVENTS","relatesTo":""},{"val":"TODOS","relatesTo":""},{"val":"NOTES","relatesTo":"","options":{"formatted":true}},{"val":"DOCUMENTS","relatesTo":""},{"val":"EMAILS","relatesTo":""}],"type":"PERSON","markerGroups":["CAREER_CONTACT_DESCRIPTOR_TAGS","CONTACTED_PIPELINE"],"newFields":["SYSTEM_NAME","IMAGE"],"includeInPages":true,"altPageName":"professionalContacts","templateRefs":["CAREER_CONTACT"],"mergeIds":["PERSON"],"folder":"Job search","specificDefaults":{"MARKERS":{"CAREER_CONTACT_DESCRIPTOR_TAGS":["referee","headhunter"]}},"views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":3},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FEATURES","slot":"REG2","id":"EVENTS","formattedId":"EVENTS_FEATURE_COUNT","name":"Events"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":5},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"},{"dataType":"FIELDS","slot":"REG2","id":"EMAILS","formattedId":"EMAILS","name":"Emails"}]}],"defaultView":"LINE","examples":[{"data":{"SYSTEM_NAME":{"fieldValue":"Sophia Craig (example)"},"PREFERRED_NAME":{"fieldValue":"Sophie"},"IMAGE":{"fieldValue":"images/defaultPeople/person1.png"},"extras":{"FULL_NAME":{"fieldValue":"Sophia Craig"},"FIRST_NAME":{"fieldValue":"Sophia"},"LAST_NAME":{"fieldValue":"Craig"}},"PHONES":{"listValue":[{"fieldValue":"(000) 657-4768","labelKey":1}]},"EMAILS":{"listValue":[{"fieldValue":"[object.object]-example@hylark.com","labelKey":1}]},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"markers":{"CAREER_CONTACT_DESCRIPTOR_TAGS":["opportunity","referee","headhunter"],"CONTACTED_PIPELINE":["contacted"]}},{"data":{"SYSTEM_NAME":{"fieldValue":"Frances Tran (example)"},"IMAGE":{"fieldValue":"images/defaultPeople/person2.png"},"extras":{"FULL_NAME":{"fieldValue":"Frances Tran"},"FIRST_NAME":{"fieldValue":"Frances"},"LAST_NAME":{"fieldValue":"Tran"}},"PHONES":{"listValue":[{"fieldValue":"(000) 258-9701","labelKey":1}]},"EMAILS":{"listValue":[{"fieldValue":"[object.object]-example@hylark.com","labelKey":1}]},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"markers":{"CAREER_CONTACT_DESCRIPTOR_TAGS":["colleague","referee","headhunter"],"CONTACTED_PIPELINE":["contacted"]}}]},{"name":"Positions","singularName":"Position","symbol":"fa-briefcase","pageType":"ENTITIES","id":"JOB_POSITION","description":"Organize and manage job positions, tracking applications and interactions with potential employers.","fields":[{"type":"SYSTEM_NAME","id":"SYSTEM_NAME","nameKey":"POSITION","name":"Position"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"DESCRIPTION","name":"Description"},{"type":"LINE","options":{"list":false,"labeled":false},"id":"ROLE","name":"Role"},{"type":"CATEGORY","options":{"category":"INDUSTRIES_TEMP","multiSelect":true,"list":false,"labeled":false},"id":"INDUSTRY","name":"Industry"},{"type":"LOCATION","options":{"multiSelect":true,"levels":["CITY","STATE","COUNTRY","CONTINENT"],"list":false,"labeled":false},"id":"WORLDWIDE_LOCATIONS","nameKey":"LOCATION","name":"Location"},{"type":"MONEY","meta":{"display":"MONEY_RANGE"},"options":{"currency":null,"list":false,"labeled":false,"isRange":true},"id":"SALARY_RANGE","name":"Salary range"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"REQUIREMENTS","name":"Requirements"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"BENEFITS","name":"Benefits"},{"type":"SELECT","meta":{"display":"RADIO_LIST"},"options":{"valueOptions":{"1":"Hybrid","2":"Remote","3":"In person"},"multiSelect":false,"list":false,"labeled":false},"id":"ATTENDANCE_TYPE","nameKey":"ATTENDANCE","name":"Attendance"},{"type":"DURATION","meta":{"display":"DURATION_RANGE"},"options":{"list":false,"labeled":false,"isRange":true},"id":"COMMUTE_DURATION","name":"Commute duration"},{"type":"LINE","options":{"list":false,"labeled":false},"id":"BONUS","name":"Bonus"}],"features":[{"val":"EVENTS","relatesTo":""},{"val":"TODOS","relatesTo":""},{"val":"NOTES","relatesTo":"","options":{"formatted":true}},{"val":"DOCUMENTS","relatesTo":""},{"val":"PRIORITIES","relatesTo":""},{"val":"FAVORITES","relatesTo":""},{"val":"TIMEKEEPER","relatesTo":""},{"val":"LINKS","relatesTo":""},{"val":"EMAILS","relatesTo":""}],"type":"ITEM","markerGroups":["APPLICATION_STATUS"],"newFields":["SYSTEM_NAME","DESCRIPTION"],"templateRefs":["JOB_POSITION"],"folder":"Job search","views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Position","combo":5},{"dataType":"MARKERS","slot":"REG1","id":"APPLICATION_STATUS","formattedId":"APPLICATION_STATUS","name":"Application status"},{"dataType":"FEATURES","slot":"REG2","id":"FAVORITES","formattedId":"FAVORITES_FAVORITES","name":"Favorites"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Position","combo":3},{"dataType":"MARKERS","slot":"REG1","id":"APPLICATION_STATUS","formattedId":"APPLICATION_STATUS","name":"Application status"},{"dataType":"FIELDS","slot":"REG2","id":"DESCRIPTION","formattedId":"DESCRIPTION","name":"Description"}]}],"defaultView":"TILE","examples":[{"data":{"SYSTEM_NAME":{"fieldValue":"Position 1"}},"markers":{"APPLICATION_STATUS":"closed"},"features":{"PRIORITIES":5,"FAVORITES":false}},{"data":{"SYSTEM_NAME":{"fieldValue":"Position 2"}},"markers":{"APPLICATION_STATUS":"identified"},"features":{"PRIORITIES":5,"FAVORITES":true}}]},{"name":"Target companies","singularName":"Target company","symbol":"fa-building-circle-check","pageType":"ENTITIES","id":"TARGET_COMPANY","description":"A centralized space for managing details and interactions with various organizations.","fields":[{"type":"SYSTEM_NAME","id":"SYSTEM_NAME","nameKey":"ORGANIZATION","name":"Organization"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"DESCRIPTION","name":"Description"},{"type":"ADDRESS","options":{"list":true,"labeled":{"freeText":true}},"id":"ADDRESSES_FREE_LABEL","nameKey":"ADDRESSES","name":"Addresses"},{"type":"EMAIL","options":{"list":true,"labeled":{"freeText":true}},"id":"EMAILS_FREE_LABEL","exampleKey":"EMAIL","nameKey":"EMAILS","name":"Emails"},{"type":"PHONE","options":{"list":true,"labeled":{"freeText":true}},"id":"PHONES_FREE_LABEL","exampleKey":"PHONE","nameKey":"PHONES","name":"Phone numbers"},{"type":"URL","options":{"list":true,"labeled":{"freeText":true}},"id":"LINKS","nameKey":"LINKS","exampleKey":"LINK","name":"Links"},{"type":"LOCATION","options":{"multiSelect":true,"levels":["CITY","STATE","COUNTRY","CONTINENT"],"list":false,"labeled":false},"id":"WORLDWIDE_LOCATIONS","nameKey":"LOCATION","name":"Location"},{"type":"IMAGE","options":{"croppable":true,"primary":true,"list":false,"labeled":false},"id":"LOGO","exampleKey":"IMAGE","name":"Logo"},{"type":"SELECT","meta":{"display":"DROPDOWN"},"options":{"valueOptions":{"1":"Charity","2":"Enterprise","3":"Large","4":"Medium","5":"Micro","6":"Not for profit","7":"PE-backed","8":"Private","9":"Public","10":"Small","11":"Startup"},"multiSelect":false,"list":false,"labeled":false},"id":"ORGANIZATION_TYPE","name":"Organization type"}],"features":[{"val":"EVENTS","relatesTo":""},{"val":"TODOS","relatesTo":""},{"val":"NOTES","relatesTo":"","options":{"formatted":true}},{"val":"DOCUMENTS","relatesTo":""},{"val":"PRIORITIES","relatesTo":""},{"val":"FAVORITES","relatesTo":""}],"type":"ITEM","newFields":["SYSTEM_NAME","DESCRIPTION"],"templateRefs":["TARGET_COMPANY"],"mergeIds":["ORGANIZATION"],"relationships":[{"name":"Positions","type":"ONE_TO_MANY","to":"JOB_POSITION","inverseName":"Organization"},{"name":"Employees","type":"MANY_TO_MANY","to":"CAREER_CONTACT","inverseName":"Organizations"}],"folder":"Job search","views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Organization","combo":5},{"dataType":"FEATURES","slot":"REG1","id":"FAVORITES","formattedId":"FAVORITES_FAVORITES","name":"Favorites"},{"dataType":"FIELDS","slot":"IMAGE1","id":"LOGO","formattedId":"LOGO","name":"Logo"},{"dataType":"FIELDS","slot":"REG2","id":"EMAILS_FREE_LABEL","formattedId":"EMAILS_FREE_LABEL","name":"Emails"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Organization","combo":4},{"dataType":"FIELDS","slot":"IMAGE1","id":"LOGO","formattedId":"LOGO","name":"Logo"},{"dataType":"FIELDS","slot":"REG1","id":"EMAILS_FREE_LABEL","formattedId":"EMAILS_FREE_LABEL","name":"Emails"},{"dataType":"FIELDS","slot":"REG2","id":"PHONES_FREE_LABEL","formattedId":"PHONES_FREE_LABEL","name":"Phone numbers"}]}],"defaultView":"SPREADSHEET","examples":[{"data":{"SYSTEM_NAME":{"fieldValue":"Target company 1"},"LOGO":{"fieldValue":"images/defaultItems/16.png"},"EMAILS_FREE_LABEL":{"listValue":[{"fieldValue":"[object.object]-example@hylark.com","label":"Example"}]},"PHONES_FREE_LABEL":{"listValue":[{"fieldValue":"(000) 178-4139","label":"Example"}]},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"features":{"PRIORITIES":9,"FAVORITES":false}},{"data":{"SYSTEM_NAME":{"fieldValue":"Target company 2"},"LOGO":{"fieldValue":"images/defaultItems/13.png"},"EMAILS_FREE_LABEL":{"listValue":[{"fieldValue":"[object.object]-example@hylark.com","label":"Example"}]},"PHONES_FREE_LABEL":{"listValue":[{"fieldValue":"(000) 103-8617","label":"Example"}]},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"features":{"PRIORITIES":1,"FAVORITES":false}}]},{"pageName":"Career documents","symbol":"fa-folders","pageType":"DOCUMENTS","id":"CAREER_DOCUMENTS","description":"Store your career-related documents like resumes, cover letters, and certificates.","includeInPages":true,"lists":["CAREER_DOCUMENTS"],"templateRefs":["CAREER_DOCUMENTS"],"folder":"Job search"},{"pageName":"Daily planner","symbol":"fa-calendar-heart","pageType":"CALENDAR","id":"GENERIC_CALENDAR","description":"An all-round versatile calendar for your schedule - keep track of activities, social engagements, appointments, and more.","lists":["PERSONAL_EVENTS","FAMILY","SOCIAL","LIFE_ADMIN","APPOINTMENTS","BIRTHDAYS"],"includeInPages":true,"templateRefs":["GENERIC_CALENDAR"]},{"pageName":"Referees","symbol":"fa-person-circle-check","pageType":"ENTITIES","id":"REFEREE_SUBSET","description":"Manage and organize your list of career referees, including their contact details and affiliations.","subset":{"mainId":"CAREER_CONTACT","filter":{"type":"MARKER","id":"CAREER_CONTACT_DESCRIPTOR_TAGS.referee","comparator":"IS"}},"specificDefaults":{"MARKERS":{"CAREER_CONTACT_DESCRIPTOR_TAGS":["referee"]}},"folder":"Job search","views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":3},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FEATURES","slot":"REG2","id":"EVENTS","formattedId":"EVENTS_FEATURE_COUNT","name":"Events"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":5},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"},{"dataType":"FIELDS","slot":"REG2","id":"EMAILS","formattedId":"EMAILS","name":"Emails"}]}],"defaultView":"TILE"},{"pageName":"Headhunters","symbol":"fa-screen-users","pageType":"ENTITIES","id":"HEADHUNTER_SUBSET","description":"Keep track of interactions and details of headhunters in your professional network.","subset":{"mainId":"CAREER_CONTACT","filter":{"type":"MARKER","id":"CAREER_CONTACT_DESCRIPTOR_TAGS.headhunter","comparator":"IS"}},"specificDefaults":{"MARKERS":{"CAREER_CONTACT_DESCRIPTOR_TAGS":["headhunter"]}},"folder":"Job search","views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":3},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FEATURES","slot":"REG2","id":"EVENTS","formattedId":"EVENTS_FEATURE_COUNT","name":"Events"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Full name","combo":5},{"dataType":"MARKERS","slot":"REG1","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","formattedId":"CAREER_CONTACT_DESCRIPTOR_TAGS","name":"Contact descriptors"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"},{"dataType":"FIELDS","slot":"REG2","id":"EMAILS","formattedId":"EMAILS","name":"Emails"}]}],"defaultView":"LINE"}],"lists":{"documents":[{"id":"CAREER_DOCUMENTS","name":"Career documents","templateRefs":["CAREER_DOCUMENTS"]}],"calendar":[{"id":"PERSONAL_EVENTS","name":"Personal","templateRefs":["PERSONAL_EVENTS"]},{"id":"FAMILY","name":"Family","templateRefs":["FAMILY"]},{"id":"SOCIAL","name":"Social","templateRefs":["SOCIAL"]},{"id":"LIFE_ADMIN","name":"Life admin","templateRefs":["LIFE_ADMIN"]},{"id":"APPOINTMENTS","name":"Appointments","templateRefs":["APPOINTMENTS"]},{"id":"BIRTHDAYS","name":"Birthdays and anniversaries","templateRefs":["BIRTHDAYS"]}]}},{"id":2,"name":"Space 2","contributors":[{"val":"GIFTS"}],"pages":[{"name":"Gifts","singularName":"Gift","symbol":"fa-gift","pageType":"ENTITIES","id":"GIFT","description":"Plan gifts for birthdays, holidays, and other special events, ensuring you always have thoughtful and appropriate gift ideas on hand, or use this page to create wish lists for yourself and others.","fields":[{"type":"SYSTEM_NAME","id":"SYSTEM_NAME","nameKey":"GIFT","name":"Gift"},{"type":"PARAGRAPH","options":{"list":false,"labeled":false},"id":"DESCRIPTION","name":"Description"},{"type":"IMAGE","options":{"croppable":true,"primary":true,"list":false,"labeled":false},"id":"IMAGE","exampleKey":"IMAGE","name":"Image"},{"type":"SELECT","meta":{"display":"DROPDOWN"},"options":{"valueOptions":{"1":"Birthday","2":"Anniversary","3":"Wedding","4":"Christmas","5":"Other"},"multiSelect":false,"list":false,"labeled":false},"id":"OCCASION","name":"Occasion"},{"type":"MONEY","meta":{"display":"MONEY_RANGE"},"options":{"currency":null,"list":false,"labeled":false,"isRange":true},"id":"ESTIMATED_COST","name":"Estimated cost"},{"type":"MONEY","options":{"currency":null,"list":false,"labeled":false},"id":"COST","name":"Cost"},{"type":"URL","options":{"list":true,"labeled":{"freeText":true}},"id":"LINKS","nameKey":"LINKS","exampleKey":"LINK","name":"Links"}],"features":[{"val":"EVENTS","relatesTo":""},{"val":"TODOS","relatesTo":""},{"val":"NOTES","relatesTo":"","options":{"formatted":true}},{"val":"DOCUMENTS","relatesTo":""},{"val":"LINKS","relatesTo":""},{"val":"PINBOARD","relatesTo":""},{"val":"PRIORITIES","relatesTo":""},{"val":"FAVORITES","relatesTo":""}],"type":"ITEM","markerGroups":["GIFT_TAGS","GIFT_STATUS"],"newFields":["SYSTEM_NAME","DESCRIPTION","IMAGE"],"includeInPages":true,"templateRefs":["GIFT"],"folder":"Gifts","views":[{"viewType":"LINE","id":"LINE","template":"Line1","name":"Line","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Gift","combo":4},{"dataType":"MARKERS","slot":"REG1","id":"GIFT_TAGS","formattedId":"GIFT_TAGS","name":"Gift tags"},{"dataType":"FEATURES","slot":"REG2","id":"FAVORITES","formattedId":"FAVORITES_FAVORITES","name":"Favorites"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"}]},{"viewType":"TILE","id":"TILE","template":"Tile1","name":"Tile","visibleData":[{"dataType":"FIELDS","slot":"HEADER1","id":"SYSTEM_NAME","formattedId":"SYSTEM_NAME","name":"Gift","combo":5},{"dataType":"MARKERS","slot":"REG1","id":"GIFT_TAGS","formattedId":"GIFT_TAGS","name":"Gift tags"},{"dataType":"FIELDS","slot":"IMAGE1","id":"IMAGE","formattedId":"IMAGE","name":"Image"},{"dataType":"FIELDS","slot":"REG2","id":"DESCRIPTION","formattedId":"DESCRIPTION","name":"Description"}]}],"defaultView":"TILE","examples":[{"data":{"SYSTEM_NAME":{"fieldValue":"Gift 1"},"IMAGE":{"fieldValue":"images/defaultItems/6.png"},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"markers":{"GIFT_TAGS":["needToThinkAboutIt"],"GIFT_STATUS":"ordered"},"features":{"PRIORITIES":9,"FAVORITES":true}},{"data":{"SYSTEM_NAME":{"fieldValue":"Gift 2"},"IMAGE":{"fieldValue":"images/defaultItems/9.png"},"LINKS":{"listValue":[{"fieldValue":"https://hylark/[object.object]-example.com","label":"Example"}]}},"markers":{"GIFT_TAGS":["needToThinkAboutIt"],"GIFT_STATUS":"ordered"},"features":{"PRIORITIES":0,"FAVORITES":false}}]},{"pageName":"Gift ideas pinboard","symbol":"fa-gifts","pageType":"PINBOARD","id":"GIFT_BOARD","description":"Use this pinboard for gather visual inspiration for gifts, including DIY ideas, products from online shops, and personalized gift concepts.","lists":["GIFT_BOARD"],"templateRefs":["GIFT_BOARD"],"includeInPages":true},{"pageName":"Gift links","symbol":"fa-hand-holding-heart","pageType":"LINKS","id":"GIFT_LINKS","description":"Keep a collection of useful links related to gift ideas, such as online stores, external wish lists, and gift guides. ","lists":["GIFT_LINKS"],"templateRefs":["GIFT_LINKS"],"includeInPages":true}],"lists":{"pinboard":[{"id":"GIFT_BOARD","name":"Gift","templateRefs":["GIFT_BOARD"]}],"links":[{"id":"GIFT_LINKS","name":"Gift links","templateRefs":["GIFT_LINKS"]}]}}],"name":"My base","markerGroups":[{"name":"Contact descriptors","id":"CAREER_CONTACT_DESCRIPTOR_TAGS","type":"TAG","templateRefs":["CAREER_CONTACT_DESCRIPTOR_TAGS"],"markers":[{"id":"acquaintance","color":"#81c4ad","name":"Acquaintance"},{"id":"friend","color":"#d5e288","name":"Friend"},{"id":"mentor","color":"#acb967","name":"Mentor"},{"id":"goodSource","color":"#e288de","name":"Good source"},{"id":"colleague","color":"#b9676c","name":"Colleague"},{"id":"manager","color":"#e2ba88","name":"Manager"},{"id":"opportunity","color":"#819fc4","name":"Opportunity"},{"id":"referee","color":"#8c67b9","name":"Referee"},{"id":"headhunter","color":"#678cb9","name":"Headhunter"}]},{"name":"Communication pipeline","id":"CONTACTED_PIPELINE","type":"PIPELINE","templateRefs":["CONTACTED_PIPELINE"],"markers":[{"id":"awaitingReply","color":"#c4ba81","name":"Awaiting reply"},{"id":"contacted","color":"#bac481","name":"Contacted"}]},{"name":"Application status","id":"APPLICATION_STATUS","type":"STATUS","templateRefs":["APPLICATION_STATUS"],"markers":[{"id":"identified","color":"#b96784","name":"Identified"},{"id":"inProgress","color":"#c4ba81","name":"In progress"},{"id":"applying","color":"#9a8686","name":"Applying"},{"id":"waitingForReply","color":"#968a8a","name":"Waiting for reply"},{"id":"interview","color":"#d44c53","name":"Interview"},{"id":"closed","color":"#9588e2","name":"Closed"},{"id":"offerMade","color":"#d5e288","name":"Offer made"},{"id":"accepted","color":"#b9b1b1","name":"Accepted"},{"id":"notApplying","color":"#c481c1","name":"Not applying"}]},{"name":"Gift tags","id":"GIFT_TAGS","type":"TAG","templateRefs":["GIFT_TAGS"],"markers":[{"id":"nothingToSeeHere","color":"#db6a70","name":"Nothing to see here"},{"id":"future","color":"#6adb70","name":"Future"},{"id":"now","color":"#b188e2","name":"Now"},{"id":"need","color":"#88cce2","name":"Need"},{"id":"want","color":"#81c498","name":"Want"},{"id":"recurring","color":"#ab9a9a","name":"Recurring"},{"id":"needToThinkAboutIt","color":"#81c484","name":"Need to think about it"},{"id":"hardToGet","color":"#d09abd","name":"Hard to get"},{"id":"handmade","color":"#88cce2","name":"Handmade"},{"id":"online","color":"#a29ad0","name":"Online"},{"id":"local","color":"#a8db6a","name":"Local"}]},{"name":"Gift status","id":"GIFT_STATUS","type":"STATUS","templateRefs":["GIFT_STATUS"],"markers":[{"id":"wait","color":"#c39ad0","name":"Wait"},{"id":"purchased","color":"#acb967","name":"Purchased"},{"id":"ordered","color":"#8c67b9","name":"Ordered"},{"id":"open","color":"#868d9a","name":"Open"},{"id":"delivered","color":"#d0c89a","name":"Delivered"}]}],"categories":[{"name":"Industries","id":"INDUSTRIES_TEMP","items":[{"name":"Business, accountancy, and finance"},{"name":"Social care and charity"},{"name":"Engineering and manufacturing"},{"name":"Environment, agriculture and animals"},{"name":"Health and wellness"},{"name":"Arts, media, and design"},{"name":"Education and training"},{"name":"Transport and logistics"},{"name":"Science"},{"name":"Leisure, tourism, and sport"},{"name":"Information technology"},{"name":"Marketing, advertising and PR"},{"name":"Law"},{"name":"Security and emergency response"},{"name":"Property and construction"},{"name":"Recruitment and HR"},{"name":"Retail"},{"name":"Sales"},{"name":"Other"}],"templateRefs":["INDUSTRIES"]}]}]', true, 512, \JSON_THROW_ON_ERROR);

    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), $body)
        ->assertSuccessful();
});

test('registering wit a collaborative base works in the queue', function () {
    config([
        'queue.default' => 'test',
        'queue.connections.test' => [
            'driver' => 'database',
            'connection' => 'test_queue',
            'table' => 'jobs',
            'queue' => 'default',
        ],
        'database.connections.test_queue' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ],
    ]);

    Schema::connection('test_queue')->create('jobs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    $user = createUser();

    $this->be($user)->postJson(route('bootstrap'), [[
        'id' => 1,
        'baseType' => BaseType::COLLABORATIVE->value,
        'spaces' => [],
        'name' => 'Collaborative base',
        'markerGroups' => [],
        'categories' => [],
    ]])->assertSuccessful();
});
