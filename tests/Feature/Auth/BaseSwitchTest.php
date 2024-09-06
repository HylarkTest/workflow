<?php

declare(strict_types=1);

use App\Models\Base;
use App\Core\BaseType;
use App\Core\Groups\Role;
use Tests\Mappings\Feature\Categories\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bases can be switched', function () {
    /** @var User $user */
    $user = createUser();

    $firstBase = $user->bases()->first();
    $base = Base::create(['name' => 'Group', 'type' => BaseType::COLLABORATIVE]);
    $user->bases()->attach($base, ['role' => Role::OWNER]);

    $this->be($user)->assertGraphQL([
        'base' => [
            'node' => ['id' => $firstBase->global_id, 'name' => 'My base'],
            'role' => 'OWNER',
        ],
    ]);

    $this->be($user)->put(route('switch-base', ['baseId' => $base->global_id]))->assertSuccessful();

    $this->be($user->fresh())->assertGraphQL([
        'base' => [
            'node' => ['id' => $base->global_id, 'name' => 'Group'],
            'role' => 'OWNER',
        ],
    ]);
});

test('a user cannot switch to a base they are not associated with', function () {
    $this->withExceptionHandling();
    /** @var User $user */
    $user = createUser();

    $firstBase = $user->bases()->first();
    $base = Base::create(['name' => 'Group', 'type' => BaseType::COLLABORATIVE]);

    $this->be($user)->putJson(route('switch-base', ['baseId' => $base->global_id]))->assertNotFound();

    $this->be($user)->assertGraphQL([
        'base' => [
            'node' => ['id' => $firstBase->global_id, 'name' => 'My base'],
            'role' => 'OWNER',
        ],
    ]);
});
