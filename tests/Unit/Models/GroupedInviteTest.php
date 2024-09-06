<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Mapping;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creating an invite saves a grouped invite', function () {
    static::markTestSkipped('Skipped until invites are working');
    /** @var \App\Models\User $invited */
    $invited = create(User::class);
    /** @var \App\Models\User $inviter */
    $inviter = create(User::class);
    /** @var \App\Models\User $secondInviter */
    $secondInviter = create(User::class);
    /** @var \App\Models\Mapping $firstMapping */
    $firstMapping = make(Mapping::class);
    /** @var \App\Models\Mapping $secondMapping */
    $secondMapping = make(Mapping::class);
    /** @var \App\Models\Mapping $thirdMapping */
    $thirdMapping = make(Mapping::class);

    $inviter->mappings()->saveMany([$firstMapping, $secondMapping]);
    $secondInviter->mappings()->save($thirdMapping);

    expect($invited->sharedDomains()->get())->toBeEmpty();

    $invite = $inviter->invite($invited, $firstMapping);

    expect($invited->sharedDomains()->get())->toBeEmpty();

    $invite->accept();

    expect($invited->sharedDomains()->get())->toHaveCount(1);

    $secondInvite = $inviter->invite($invited, $secondMapping, true);

    expect($invited->sharedDomains()->get())->toHaveCount(1);

    $secondInviter->invite($invited, $thirdMapping, true);

    expect($invited->sharedDomains()->get())->toHaveCount(2);

    $invite->delete();

    expect($invited->sharedDomains()->get())->toHaveCount(2);

    $secondInvite->delete();

    expect($invited->sharedDomains()->get())->toHaveCount(1);
});
