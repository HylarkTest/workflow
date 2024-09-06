<?php

declare(strict_types=1);

use App\Models\Location;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

uses()->group('location');

test('a mapping can have a location type field', function () {
    $this->assertFieldCreated(FieldType::LOCATION());
});

test('a location field can be restricted to certain levels', function () {
    $city = Location::find(2647793);
    $this->assertValidFieldRequest(
        FieldType::LOCATION(),
        ['levels' => ['CITY']],
        ['field' => ['fieldValue' => $city->globalId()]],
        ['field' => ['fieldValue' => ['id' => $city->globalId(), 'name' => 'Guildford (GB)']]],
        'field { fieldValue { id name } }'
    );
});

test('a location field can be restricted to certain countries', function () {
    $country = Location::find(2635167);
    $city = Location::find(2647793);

    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        ['countries' => [$country->global_id]],
        ['fieldValue' => $city->globalId()],
        ['fieldValue' => ['id' => $city->globalId(), 'name' => 'Guildford (GB)']],
        ['_v' => $city->id],
        'field { fieldValue { id name } }'
    );
});

test('a location field can be saved on an item', function () {
    $location = Location::find(2635167);
    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        [],
        ['fieldValue' => $location->globalId()],
        ['fieldValue' => ['id' => $location->globalId(), 'name' => 'United Kingdom']],
        ['_v' => $location->id],
        'field { fieldValue { id name } }'
    );
});

test('a location field can be updated on an item', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);
    $this->assertItemUpdatedWithField(
        FieldType::LOCATION(),
        [],
        ['_v' => $firstLocation->globalId()],
        ['fieldValue' => $secondLocation->globalId()],
        ['fieldValue' => ['id' => $secondLocation->globalId(), 'name' => 'United States']],
        ['_v' => $secondLocation->id],
        'field { fieldValue { id name } }'
    );
});

test('a location can be removed from an item', function () {
    $location = Location::find(2635167);
    $this->assertItemUpdatedWithField(
        FieldType::LOCATION(),
        ['labeled' => ['freeText' => true]],
        ['label' => 'Test', 'fieldValue' => $location->globalId()],
        ['label' => 'Test', 'fieldValue' => null],
        ['label' => 'Test', 'fieldValue' => null],
        ['label' => 'Test'],
        'field { label fieldValue { id name } }'
    );
});

test('a location from a different level cannot be saved to a location field', function () {
    $user = createUser();
    $country = Location::find(2635167);
    $this->be($user)->assertInvalidFieldRequest(
        FieldType::LOCATION(),
        ['levels' => ['CITY']],
        ['field' => ['fieldValue' => $country->globalId()]],
        ['input.data.field.fieldValue' => ['The selected "field" is invalid.']],
        'field { fieldValue { id name } }'
    );
});

test('a location from a different country cannot be saved to a location field', function () {
    $user = createUser();
    $country = Location::find(2635167);
    $city = Location::find(2988507);
    $this->be($user)->assertInvalidFieldRequest(
        FieldType::LOCATION(),
        ['countries' => [$country->getKey()]],
        ['field' => ['fieldValue' => $city->globalId()]],
        ['input.data.field.fieldValue' => ['The selected "field" is invalid.']],
        'field { fieldValue { id name } }'
    );
});

test('a location field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::LOCATION(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { id name } }'
    );
});

test('a location field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LOCATION(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { id name } }'
    );
});

test('the levels must be valid values', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::LOCATION(),
        ['levels' => ['9']],
        ['input.options.levels.0' => ['The selected level is invalid.']],
    );
});

test('the countries must be valid countries', function () {
    $city = Location::find(2988507);
    $this->assertInvalidAddFieldRequest(
        FieldType::LOCATION(),
        ['countries' => ['BAD', $city->global_id]],
        [
            'input.options.countries.0' => ['The selected country is invalid.'],
            'input.options.countries.1' => ['The selected country is invalid.'],
        ],
    );
});

test('a locations field can have labels', function () {
    $city = Location::find(2647793);
    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        ['labeled' => ['freeText' => true]],
        ['label' => 'Label', 'fieldValue' => $city->globalId()],
        ['label' => 'Label', 'fieldValue' => ['id' => $city->globalId(), 'name' => 'Guildford (GB)']],
        ['label' => 'Label', 'fieldValue' => $city->id],
        'field { label fieldValue { id name } }'
    );
});

test('a locations field can have multi', function () {
    $city = Location::find(2647793);
    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        ['multiSelect' => true],
        ['fieldValue' => [$city->globalId()]],
        ['fieldValue' => [['id' => $city->globalId(), 'name' => 'Guildford (GB)']]],
        ['_v' => [$city->id]],
        'field { fieldValue { id name } }'
    );
});

test('a locations field can have labels with multi', function () {
    $city = Location::find(2647793);
    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        [
            'labeled' => ['freeText' => true],
            'multiSelect' => true,
        ],
        ['label' => 'Label', 'fieldValue' => [$city->globalId()]],
        ['label' => 'Label', 'fieldValue' => [['id' => $city->globalId(), 'name' => 'Guildford (GB)']]],
        ['label' => 'Label', 'fieldValue' => [$city->id]],
        'field { label fieldValue { id name } }'
    );
});

test('a locations field can be labeled with a list', function () {
    $city = Location::find(2647793);
    $this->assertItemCreatedWithField(
        FieldType::LOCATION(),
        [
            'labeled' => ['freeText' => true],
            'list' => true,
        ],
        ['listValue' => [['label' => 'Label', 'fieldValue' => $city->globalId()]]],
        ['listValue' => [['label' => 'Label', 'fieldValue' => ['id' => $city->globalId(), 'name' => 'Guildford (GB)']]]],
        ['listValue' => [['label' => 'Label', 'fieldValue' => $city->id]]],
        'field { listValue { label fieldValue { id name } } }'
    );
});

test('location field actions are formatted correctly', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);

    $this->assertItemUpdateCreatedActions(
        FieldType::LOCATION(),
        [],
        ['fieldValue' => $firstLocation->id],
        ['fieldValue' => $secondLocation->id],
        ['after' => 'United Kingdom'],
        ['before' => 'United Kingdom', 'after' => 'United States'],
    );
});

test('multi select location field actions are formatted correctly', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);

    $this->assertItemUpdateCreatedActions(
        FieldType::LOCATION(),
        ['multiSelect' => true],
        ['fieldValue' => [$firstLocation->id]],
        ['fieldValue' => [$firstLocation->id, $secondLocation->id]],
        ['after' => 'United Kingdom'],
        ['before' => 'United Kingdom', 'after' => 'United Kingdom, United States'],
    );
});

test('labeled multi select location field actions are formatted correctly', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);

    $this->assertItemUpdateCreatedActions(
        FieldType::LOCATION(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true]],
        ['label' => 'Test', 'fieldValue' => [$firstLocation->id]],
        ['label' => 'Test2', 'fieldValue' => [$firstLocation->id, $secondLocation->id]],
        ['after' => '[Test]: United Kingdom'],
        ['before' => '[Test]: United Kingdom', 'after' => '[Test2]: United Kingdom, United States'],
    );
});

test('list multi select location field actions are formatted correctly', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);

    $this->assertItemUpdateCreatedActions(
        FieldType::LOCATION(),
        ['multiSelect' => true, 'list' => true],
        ['listValue' => [['fieldValue' => [$firstLocation->id]]]],
        ['listValue' => [['fieldValue' => [$secondLocation->id]], ['fieldValue' => [$firstLocation->id, $secondLocation->id]]]],
        ['after' => 'United Kingdom', 'type' => 'paragraph'],
        ['before' => 'United Kingdom', 'after' => "United States\nUnited Kingdom, United States", 'type' => 'paragraph'],
    );
});

test('list labeled multi select location field actions are formatted correctly', function () {
    $firstLocation = Location::find(2635167);
    $secondLocation = Location::find(6252001);

    $this->assertItemUpdateCreatedActions(
        FieldType::LOCATION(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true], 'list' => true],
        ['listValue' => [['label' => 'Test', 'fieldValue' => [$firstLocation->id]]]],
        ['listValue' => [
            ['label' => 'TestA', 'fieldValue' => [$secondLocation->id]],
            ['label' => 'Test2', 'fieldValue' => [$firstLocation->id, $secondLocation->id]],
        ]],
        ['after' => '[Test]: United Kingdom', 'type' => 'paragraph'],
        ['before' => '[Test]: United Kingdom', 'after' => "[TestA]: United States\n[Test2]: United Kingdom, United States", 'type' => 'paragraph'],
    );
});

test('items can be sorted by location field', function () {
    $abbotsford = Location::find(5881791);
    $barrie = Location::find(5894171);
    $calgary = Location::find(5913490);
    $this->assertFieldIsSortable(
        FieldType::LOCATION(),
        [],
        [$abbotsford->id, $calgary->id, $barrie->id],
    );
})->group('es');
