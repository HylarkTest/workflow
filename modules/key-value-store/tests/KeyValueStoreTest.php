<?php

declare(strict_types=1);

namespace Tests\KeyValueStore;

use KeyValueStore\KeyValueStore;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Redis;
use KeyValueStore\KeyValueStoreServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

class KeyValueStoreTest extends TestCase
{
    public function createUser($attributes = []): User
    {
        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = User::query()->forceCreate(array_merge([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ], $attributes));

        return $user;
    }

    /**
     * A user can store something in redis
     *
     * @test
     */
    public function a_user_can_store_something_in_redis(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value']);

        $this->actingAs($user)->getJson(route('store.show', ['key' => 'key1']))
            ->assertJson(['data' => 'value']);
    }

    /**
     * A user can delete something in redis
     *
     * @test
     */
    public function a_user_can_delete_something_in_redis(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value']);

        $this->actingAs($user)->getJson(route('store.show', ['key' => 'key2']))
            ->assertJson(['data' => 'value']);

        $this->actingAs($user)->deleteJson(route('store.destroy', ['key' => 'key2']));

        $this->actingAs($user)->getJson(route('store.show', ['key' => 'key2']))
            ->assertJson(['data' => null]);
    }

    /**
     * A user can retrieve all their keys on redis
     *
     * @test
     */
    public function a_user_can_retrieve_all_their_keys_on_redis(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->postJson(route('store.store', ['key' => 'key_a']), ['value' => 'value1']);
        usleep(100);
        $this->actingAs($user)->postJson(route('store.store', ['key' => 'key_b']), ['value' => 'value2']);

        $keys = $this->actingAs($user)->getJson(route('store.index'))->json('data');

        expect($keys)->toContain('key_a', 'key_b');
    }

    /**
     * A user cannot store a value that is too big
     *
     * @test
     */
    public function a_user_cannot_store_a_value_that_is_too_big(): void
    {
        $this->withExceptionHandling();
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson(route('store.store', ['key' => 'key3']), [
                'value' => str_pad('', 102401, 'a'),
            ])
            ->assertStatus(422);
    }

    /**
     * A user cannot store a value if they have too many keys
     *
     * @test
     */
    public function a_user_cannot_store_a_value_if_they_have_too_many_keys(): void
    {
        $this->withExceptionHandling();
        $user = $this->createUser();
        config(['key-value-store.max-keys' => 10]);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->postJson(route('store.store', ['key' => "key4_$i"]), [
                    'value' => $i,
                ])
                ->assertSuccessful();
        }

        $this->actingAs($user)
            ->postJson(route('store.store', ['key' => 'key4_11']), [
                'value' => 11,
            ])
            ->assertStatus(422);
    }

    /**
     * A user can store keys for a limited time
     *
     * @test
     */
    public function a_user_can_store_keys_for_a_limited_time(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson(route('store.store', ['key' => 'key5', 'ttl' => 1]), [
                'value' => 'value',
            ])
            ->assertSuccessful();

        $this->actingAs($user)->getJson(route('store.show', ['key' => 'key5']))
            ->assertJson(['data' => 'value']);

        sleep(1);

        $this->actingAs($user)->getJson(route('store.show', ['key' => 'key5']))
            ->assertJson(['data' => null]);
    }

    /**
     * Keys are scoped to the authenticated user
     *
     * @test
     */
    public function keys_are_scoped_to_the_authenticated_user(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser(['email' => 'l@r.ry2']);

        $this->actingAs($userA)
            ->postJson(route('store.store', ['key' => 'key6']), [
                'value' => 'valueA',
            ]);

        $this->actingAs($userB)
            ->postJson(route('store.store', ['key' => 'key6']), [
                'value' => 'valueB',
            ]);

        $this->actingAs($userA)->getJson(route('store.show', ['key' => 'key6']))
            ->assertJson(['data' => 'valueA']);
        $this->actingAs($userB)->getJson(route('store.show', ['key' => 'key6']))
            ->assertJson(['data' => 'valueB']);
    }

    /**
     * A guest cannot store or access keys
     *
     * @test
     */
    public function a_guest_cannot_store_or_access_keys(): void
    {
        $this->withExceptionHandling();
        $this->postJson(route('store.store', ['key' => 'key7']), ['data' => 'value'])
            ->assertStatus(401);
        $this->getJson(route('store.show', ['key' => 'key7']))
            ->assertStatus(401);
    }

    protected function getPackageProviders($app): array
    {
        return [
            KeyValueStoreServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/../../..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        if (getenv('TEST_TOKEN') !== false) {
            config([
                'key-value-store.key-prefix' => 'store'.getenv('TEST_TOKEN').'-',
            ]);
        }
    }

    protected function tearDown(): void
    {
        resolve(KeyValueStore::class)->deleteAll();
        parent::tearDown();
    }
}
