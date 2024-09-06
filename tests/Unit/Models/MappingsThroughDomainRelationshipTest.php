<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Space;
use App\Models\Mapping;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the mappings can be fetched through spaces in one relationship', function () {
    static::markTestSkipped('Skipped until invites are working');
    /** @var \App\Models\User $user */
    $user = create(User::class);
    /** @var \App\Models\User $inviter */
    $inviter = create(User::class);

    /** @var \App\Models\Mapping $mapping */
    $mapping = make(Mapping::class);
    /** @var \App\Models\Space $space */
    $space = make(Space::class);
    /** @var \App\Models\Mapping $mappingOnSpace */
    $mappingOnSpace = make(Mapping::class);

    $inviter->spaces()->save($space);
    $mappingOnSpace->space()->associate($space);
    $inviter->mappings()->saveMany([$mapping, $mappingOnSpace]);

    $inviter->invite($user, $mapping, true);
    $inviter->invite($user, $space, true);

    expect($user->sharedDomains->first()->allMappings)->toHaveCount(2);

    $domains = $user->sharedDomains->fresh()->load('allMappings');
    expect($domains->first()->getRelation('allMappings'))->toHaveCount(2);
});
