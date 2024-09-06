<?php

declare(strict_types=1);

use App\Models\Base;
use App\Models\User;
use App\Models\Space;
use App\Core\BaseType;
use App\Models\Invite;
use App\Models\Mapping;
use Illuminate\Support\Str;
use App\Models\GroupedInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('invites are grouped in the grouped invites table', function () {
    static::markTestSkipped('Skipped until invites are working');
    User::withoutEvents(function () {
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\User> $users */
        $users = create(User::class, [], 2);
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Base> $bases */
        $bases = create(Base::class, ['type' => BaseType::COLLABORATIVE], 2);
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Mapping> $mappings */
        $mappings = make(Mapping::class, [], 8);
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Space> $spaces */
        $spaces = make(Space::class, [], 8);

        foreach ($users as $key => $user) {
            $user->bases()->attach($bases[$key]);
            /** @var \App\Models\User $otherUser */
            $otherUser = $users[$key === 0 ? 1 : 0];
            $domainKey = $key * 2;
            $base = $user->firstPersonalBase();
            $userMappings = $base->mappings()->saveMany($mappings->slice($domainKey, 2));
            $userSpaces = $base->spaces()->saveMany($spaces->slice($domainKey, 2));
            $teamDomainKey = ($key * 2) + 4;
            $teamMappings = $bases[$key]?->base->mappings()->saveMany($mappings->slice($teamDomainKey, 2));
            $teamSpaces = $bases[$key]?->base->spaces()->saveMany($spaces->slice($teamDomainKey, 2));

            foreach (['user', 'team'] as $ownerType) {
                foreach (['mappings', 'spaces'] as $domainType) {
                    $domains = ${Str::camel($ownerType.'-'.$domainType)};
                    foreach ($domains as $domain) {
                        $user->invite($otherUser, $domain, true);
                    }
                }
            }
        }

        $unacceptedMapping = make(Mapping::class);
        /** @var \App\Models\User $firstUser */
        $firstUser = $users[0];
        $firstUser->firstPersonalBase()->mappings()->save($unacceptedMapping);
        $firstUser->invite($users[1], $unacceptedMapping);
    });

    $this->artisan('db:health:grouped-invite', ['--reset' => null]);

    static::assertSame([
        ['id' => 3, 'invited_id' => '1', 'owner_type' => 'teams', 'owner_id' => '2'],
        ['id' => 4, 'invited_id' => '1', 'owner_type' => 'users', 'owner_id' => '2'],
        ['id' => 1, 'invited_id' => '2', 'owner_type' => 'teams', 'owner_id' => '1'],
        ['id' => 2, 'invited_id' => '2', 'owner_type' => 'users', 'owner_id' => '1'],
    ], GroupedInvite::all(['id', 'invited_id', 'owner_type', 'owner_id'])->toArray());

    static::assertSame([
        ['id' => 1, 'grouped_invite_id' => '2', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'mappings', 'domain_id' => '1'],
        ['id' => 2, 'grouped_invite_id' => '2', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'mappings', 'domain_id' => '2'],
        ['id' => 3, 'grouped_invite_id' => '2', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'spaces', 'domain_id' => '1'],
        ['id' => 4, 'grouped_invite_id' => '2', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'spaces', 'domain_id' => '2'],
        ['id' => 5, 'grouped_invite_id' => '1', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'mappings', 'domain_id' => '3'],
        ['id' => 6, 'grouped_invite_id' => '1', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'mappings', 'domain_id' => '4'],
        ['id' => 7, 'grouped_invite_id' => '1', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'spaces', 'domain_id' => '3'],
        ['id' => 8, 'grouped_invite_id' => '1', 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'spaces', 'domain_id' => '4'],
        ['id' => 9, 'grouped_invite_id' => '4', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'mappings', 'domain_id' => '5'],
        ['id' => 10, 'grouped_invite_id' => '4', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'mappings', 'domain_id' => '6'],
        ['id' => 11, 'grouped_invite_id' => '4', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'spaces', 'domain_id' => '5'],
        ['id' => 12, 'grouped_invite_id' => '4', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'spaces', 'domain_id' => '6'],
        ['id' => 13, 'grouped_invite_id' => '3', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'mappings', 'domain_id' => '7'],
        ['id' => 14, 'grouped_invite_id' => '3', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'mappings', 'domain_id' => '8'],
        ['id' => 15, 'grouped_invite_id' => '3', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'spaces', 'domain_id' => '7'],
        ['id' => 16, 'grouped_invite_id' => '3', 'invited_id' => '1', 'inviter_id' => '2', 'domain_type' => 'spaces', 'domain_id' => '8'],
        ['id' => 17, 'grouped_invite_id' => null, 'invited_id' => '2', 'inviter_id' => '1', 'domain_type' => 'mappings', 'domain_id' => '9'],
    ], Invite::all(['id', 'grouped_invite_id', 'invited_id', 'inviter_id', 'domain_type', 'domain_id'])->toArray());
});
