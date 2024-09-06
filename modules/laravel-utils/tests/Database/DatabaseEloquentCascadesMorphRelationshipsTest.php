<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use PHPUnit\Framework\TestCase;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Concerns\CascadesMorphRelationships;

class DatabaseEloquentCascadesMorphRelationshipsTest extends TestCase
{
    /**
     * Set up the database schema.
     */
    public function createSchema(): void
    {
        $this->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamps();
        });

        $this->schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->morphs('postable');
            $table->timestamps();
        });

        $this->schema()->create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->timestamps();
        });

        $this->schema()->create('taggables', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('taggable');
            $table->foreignIdFor(CascadesMorphRelationshipsTestMarker::class);
            $table->timestamps();
        });
    }

    /**
     * When a user is deleted all their posts are deleted
     *
     * @test
     */
    public function when_a_user_is_deleted_all_their_posts_are_deleted(): void
    {
        /** @var \Tests\LaravelUtils\Database\CascadesMorphRelationshipsTestUser $user */
        $user = CascadesMorphRelationshipsTestUser::query()->create(['id' => 1, 'email' => 'me@example.com']);

        $post = $user->posts()->create(['text' => 'A post']);

        $user->delete();

        static::assertNull($post->fresh());
    }

    /**
     * When a user is deleted all their mappings are deleted
     *
     * @test
     */
    public function when_a_user_is_deleted_all_their_tags_are_detached(): void
    {
        /** @var \Tests\LaravelUtils\Database\CascadesMorphRelationshipsTestUser $user */
        $user = CascadesMorphRelationshipsTestUser::query()->create(['id' => 1, 'email' => 'me@example.com']);

        $user->tags()->create(['text' => 'Another tag']);

        static::assertCount(1, $user->tags);

        $user->delete();

        static::assertEmpty($user->tags()->get());
    }

    /**
     * Get a database connection instance.
     */
    protected function connection(): Connection
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = Eloquent::getConnectionResolver()->connection();

        return $connection;
    }

    /**
     * Get a schema builder instance.
     */
    protected function schema(): Builder
    {
        return $this->connection()->getSchemaBuilder();
    }

    protected function setUp(): void
    {
        $db = new DB;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $eventDispatcher = Eloquent::getEventDispatcher() ?: new Dispatcher;

        $db->setEventDispatcher($eventDispatcher);
        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();
    }

    /**
     * Tear down the database schema.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('users');
        $this->schema()->drop('posts');
        $this->schema()->drop('tags');
        $this->schema()->drop('taggables');
    }
}

/**
 * Eloquent Models...
 *
 * @property int $id
 * @property \Illuminate\Database\Eloquent\Collection<int, \Tests\LaravelUtils\Database\CascadesMorphRelationshipsTestMarker> $tags
 */
class CascadesMorphRelationshipsTestUser extends Eloquent
{
    use CascadesMorphRelationships;

    protected $table = 'users';

    protected $guarded = [];

    protected array $cascadeRelationships = [
        'tags',
        'posts',
    ];

    public function posts(): MorphMany
    {
        return $this->morphMany(CascadesMorphRelationshipsTestPost::class, 'postable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(CascadesMorphRelationshipsTestMarker::class, 'taggable');
    }
}

/**
 * @property int $id
 */
class CascadesMorphRelationshipsTestMarker extends Eloquent
{
    protected $table = 'tags';

    protected $guarded = [];
}

/**
 * @property int $id
 */
class CascadesMorphRelationshipsTestPost extends Eloquent
{
    protected $table = 'posts';

    protected $guarded = [];
}
