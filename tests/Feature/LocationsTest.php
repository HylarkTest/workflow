<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses()->group('location');

test('a user can fetch locations', function () {
    $this->rethrowGraphQLErrors();
    $user = createUser();

    $this->be($user)->graphQL('
    {
        locations(first: 10, levels: [CITY, STATE, COUNTRY, CONTINENT]) {
            edges {
                node {
                    id
                    name
                    level
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL()
        ->assertJson(['data' => ['locations' => ['edges' => [
            ['node' => ['name' => 'Africa']],
            ['node' => ['name' => 'Antarctica']],
            ['node' => ['name' => 'Asia']],
            ['node' => ['name' => 'Europe']],
            ['node' => ['name' => 'North America']],
            ['node' => ['name' => 'Oceania']],
            ['node' => ['name' => 'South America']],
            ['node' => ['name' => 'Afghanistan']],
            ['node' => ['name' => 'Aland Islands']],
            ['node' => ['name' => 'Albania']],
        ]]]]);
});

test('locations can be filtered by level', function () {
    $this->rethrowGraphQLErrors();
    $user = createUser();

    $this->be($user)->graphQL('
    {
        locations(first: 10, levels: [COUNTRY]) {
            edges {
                node {
                    id
                    name
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL()
        ->assertJson(['data' => ['locations' => ['edges' => [
            ['node' => ['name' => 'Afghanistan']],
            ['node' => ['name' => 'Aland Islands']],
            ['node' => ['name' => 'Albania']],
            ['node' => ['name' => 'Algeria']],
            ['node' => ['name' => 'American Samoa']],
            ['node' => ['name' => 'Andorra']],
            ['node' => ['name' => 'Angola']],
            ['node' => ['name' => 'Anguilla']],
            ['node' => ['name' => 'Antarctica']],
            ['node' => ['name' => 'Antigua and Barbuda']],
        ]]]]);
});

test('locations can be filtered by country', function () {
    $this->rethrowGraphQLErrors();
    $user = createUser();

    $this->be($user)->graphQL('
    {
        locations(first: 10, levels: [STATE], country: "TG9jYXRpb246NjI1MjAwMQ==") {
            edges {
                node {
                    id
                    name
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL()
        ->assertJson(['data' => ['locations' => ['edges' => [
            ['node' => ['name' => 'Alabama (US)']],
            ['node' => ['name' => 'Alaska (US)']],
            ['node' => ['name' => 'Arizona (US)']],
            ['node' => ['name' => 'Arkansas (US)']],
            ['node' => ['name' => 'California (US)']],
            ['node' => ['name' => 'Colorado (US)']],
            ['node' => ['name' => 'Connecticut (US)']],
            ['node' => ['name' => 'Delaware (US)']],
            ['node' => ['name' => 'District of Columbia (US)']],
            ['node' => ['name' => 'Florida (US)']],
        ]]]]);
});

test('locations can be searched', function () {
    $this->rethrowGraphQLErrors();
    $user = createUser();

    $this->be($user)->graphQL('
    {
        locations(first: 10, search: "London") {
            edges {
                node {
                    id
                    name
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL()
        ->assertJson(['data' => ['locations' => ['edges' => [
            ['node' => ['name' => 'London (GB)']],
            ['node' => ['name' => 'Greater London (GB)']],
            ['node' => ['name' => 'East London (ZA)']],
            ['node' => ['name' => 'London (CA)']],
            ['node' => ['name' => 'Londonderry County Borough (GB)']],
            ['node' => ['name' => 'New London, CT']],
            ['node' => ['name' => 'Little London (JM)']],
            ['node' => ['name' => 'Londonderry (LC)']],
            ['node' => ['name' => 'London Road (LC)']],
        ]]]]);
});
