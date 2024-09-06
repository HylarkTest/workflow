<?php

declare(strict_types=1);

namespace Tests\KeyValueStore;

use KeyValueStore\KeyValueStore;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Auth\User;
use KeyValueStore\Commands\StoreCommand;
use KeyValueStore\KeyValueStoreServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

class StoreCommandTest extends TestCase
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
     * @test
     */
    public function a_users_store_can_be_cleared(): void
    {
        $user1 = $this->createUser(['email' => 'l@r.ry']);
        $user2 = $this->createUser(['email' => 'l@r.ry2']);

        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key']), ['value' => 'value1']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key']), ['value' => 'value2']);

        $this->artisan(StoreCommand::class, ['action' => 'clear', '--scope' => (string) $user1->getKey()]);

        $store = resolve(KeyValueStore::class);
        expect($store->getKeysForScope((string) $user1->getKey()))->toBeEmpty()
            ->and($store->getKeysForScope((string) $user2->getKey()))->toHaveCount(1);
    }

    /**
     * @test
     */
    public function a_single_key_on_a_users_store_can_be_cleared(): void
    {
        $user1 = $this->createUser(['email' => 'l@r.ry']);
        $user2 = $this->createUser(['email' => 'l@r.ry2']);

        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value1']);
        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value2']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value3']);

        $this->artisan(StoreCommand::class, ['action' => 'clear', '--scope' => (string) $user1->getKey(), '--key' => 'key1']);

        $store = resolve(KeyValueStore::class);
        expect($store->getKeysForScope((string) $user1->getKey()))->toHaveCount(1)
            ->and($store->getKeysForScope((string) $user2->getKey()))->toHaveCount(1);
    }

    /**
     * @test
     */
    public function a_single_key_across_users_can_be_cleared(): void
    {
        $user1 = $this->createUser(['email' => 'l@r.ry']);
        $user2 = $this->createUser(['email' => 'l@r.ry2']);

        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value1']);
        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value2']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value3']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value4']);

        $this->artisan(StoreCommand::class, ['action' => 'clear', '--key' => 'key1']);

        $store = resolve(KeyValueStore::class);
        expect($store->getKeysForScope((string) $user1->getKey()))->toHaveCount(1)
            ->and($store->getKeysForScope((string) $user2->getKey()))->toHaveCount(1);
    }

    /**
     * @test
     */
    public function all_keys_can_be_cleared(): void
    {
        $user1 = $this->createUser(['email' => 'l@r.ry']);
        $user2 = $this->createUser(['email' => 'l@r.ry2']);

        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value1']);
        $this->actingAs($user1)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value2']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key1']), ['value' => 'value3']);
        $this->actingAs($user2)->postJson(route('store.store', ['key' => 'key2']), ['value' => 'value4']);

        $this->artisan(StoreCommand::class, ['action' => 'clear']);

        $store = resolve(KeyValueStore::class);
        expect($store->getKeysForScope((string) $user1->getKey()))->toBeEmpty()
            ->and($store->getKeysForScope((string) $user2->getKey()))->toBeEmpty();
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
    }

    protected function tearDown(): void
    {
        resolve(KeyValueStore::class)->deleteAll();
        parent::tearDown();
    }
}
