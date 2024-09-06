<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;
use LaravelUtils\Database\Eloquent\Relations\BelongsToOne;
use LaravelUtils\Database\Eloquent\Concerns\HasAdvancedRelationships;

class DatabaseEloquentAdvancedRelationsTest extends TestCase
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
            $table->timestamps();
        });

        $this->schema()->create('posts_users', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(AdvancedRelationsTestUser::class);
            $table->foreignIdFor(AdvancedRelationsTestPost::class);
            $table->timestamps();
        });

        $this->schema()->create('markers', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->timestamps();
        });

        $this->schema()->create('markables', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('markable');
            $table->foreignIdFor(AdvancedRelationsTestMarker::class);
            $table->timestamps();
        });
    }

    /**
     * @test
     */
    public function it_loads_relationships_automatically(): void
    {
        $this->seedData();

        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarkerWithSingleWith $marker */
        $marker = AdvancedRelationsTestMarkerWithSingleWith::query()->first();

        static::assertTrue($marker->relationLoaded('post'));
        static::assertTrue($marker->post->is(AdvancedRelationsTestPost::query()->first()));
    }

    /**
     * @test
     */
    public function it_loads_nested_relationships_automatically(): void
    {
        $this->seedData();

        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker */
        $marker = AdvancedRelationsTestMarkerWithNestedWith::query()->first();

        static::assertTrue($marker->relationLoaded('post'));
        static::assertTrue($marker->post->relationLoaded('owner'));

        static::assertTrue($marker->post->owner->is(AdvancedRelationsTestUser::query()->first()));
    }

    /**
     * @test
     */
    public function it_loads_nested_relationships_on_demand(): void
    {
        $this->seedData();

        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker */
        $marker = AdvancedRelationsTestMarker::with('post.owner')->first();

        static::assertTrue($marker->relationLoaded('post'));
        static::assertTrue($marker->post->relationLoaded('owner'));

        static::assertTrue($marker->post->owner->is(AdvancedRelationsTestUser::query()->first()));
    }

    /**
     * Models can be attached
     *
     * @test
     */
    public function models_can_be_attached(): void
    {
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $user */
        $user = AdvancedRelationsTestUser::query()->create(['email' => 'me@example.com']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post */
        $post = AdvancedRelationsTestPost::query()->create(['text' => 'A post']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post2 */
        $post2 = AdvancedRelationsTestPost::query()->create(['text' => 'Another post']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker */
        $marker = AdvancedRelationsTestMarker::query()->create(['text' => 'A marker']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker2 */
        $marker2 = AdvancedRelationsTestMarker::query()->create(['text' => 'Another marker']);

        $user->marker()->attach($marker->id);
        $post->marker()->attach($marker->id);
        $post->marker()->attach($marker2->id);

        $user->post()->attach($post->id);
        $user->post()->attach($post2->id);

        static::assertTrue($marker->is($user->marker));
        static::assertTrue($marker2->is($post->marker));
        static::assertTrue($marker->user->is($user));
        static::assertTrue($marker2->post->is($post));
        static::assertNull($marker->post);
        static::assertTrue($user->post->is($post2));
        static::assertTrue($post2->owner->is($user));
        static::assertNull($post->owner);
    }

    /**
     * Models can be eager loaded
     *
     * @test
     */
    public function models_can_be_eager_loaded(): void
    {
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $user */
        $user = AdvancedRelationsTestUser::query()->create(['email' => 'me@example.com']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post */
        $post = AdvancedRelationsTestPost::query()->create(['text' => 'A post']);
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker */
        $marker = AdvancedRelationsTestMarker::query()->create(['text' => 'A marker']);
        $post->marker()->attach($marker->id);
        $user->post()->attach($post->id);

        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $user */
        $user = AdvancedRelationsTestUser::with('post')->first();
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post */
        $post = AdvancedRelationsTestPost::with('marker')->first();
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker */
        $marker = AdvancedRelationsTestMarker::with('post')->first();

        static::assertTrue($user->relationLoaded('post'));
        static::assertTrue($post->relationLoaded('marker'));
        static::assertTrue($marker->relationLoaded('post'));
        static::assertTrue($user->post->is($post));
        static::assertTrue($post->marker->is($marker));
        static::assertTrue($marker->post->is($post));
    }

    /**
     * Helpers...
     */
    protected function seedData(): void
    {
        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $user */
        $user = AdvancedRelationsTestUser::query()->create(['id' => 1, 'email' => 'me@example.com']);

        /** @var \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post */
        $post = $user->post()->create(['text' => 'A post']);
        $post->marker()->create(['text' => 'A marker']);

        $user->marker()->create(['text' => 'Another marker']);
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
        $this->schema()->drop('posts_users');
        $this->schema()->drop('markers');
        $this->schema()->drop('markables');
    }
}

/**
 * Eloquent Models...
 *
 * @property int $id
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker
 */
class AdvancedRelationsTestUser extends Eloquent
{
    use HasAdvancedRelationships;

    protected $table = 'users';

    protected $guarded = [];

    public function post(): BelongsToOne
    {
        return $this->belongsToOne(AdvancedRelationsTestPost::class, 'posts_users');
    }

    public function marker(): MorphToOne
    {
        return $this->morphToOne(AdvancedRelationsTestMarker::class, 'markable');
    }
}

/**
 * Eloquent Models...
 *
 * @property int $id
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestMarker $marker
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $owner
 */
class AdvancedRelationsTestPost extends Eloquent
{
    use HasAdvancedRelationships;

    protected $table = 'posts';

    protected $guarded = [];

    public function marker(): MorphToOne
    {
        return $this->morphToOne(AdvancedRelationsTestMarker::class, 'markable');
    }

    public function owner(): BelongsToOne
    {
        return $this->belongsToOne(AdvancedRelationsTestUser::class, 'posts_users');
    }
}

/**
 * @property int $id
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestPost $post
 * @property \Tests\LaravelUtils\Database\AdvancedRelationsTestUser $user
 */
class AdvancedRelationsTestMarker extends Eloquent
{
    use HasAdvancedRelationships;

    protected $table = 'markers';

    protected $guarded = [];

    public function user(): MorphToOne
    {
        return $this->morphedByOne(AdvancedRelationsTestUser::class, 'markable', 'markables', 'advanced_relations_test_marker_id', 'markable_id');
    }

    public function post(): MorphToOne
    {
        return $this->morphedByOne(AdvancedRelationsTestPost::class, 'markable', 'markables', 'advanced_relations_test_marker_id', 'markable_id');
    }
}

class AdvancedRelationsTestMarkerWithSingleWith extends AdvancedRelationsTestMarker
{
    protected $with = ['post'];
}

class AdvancedRelationsTestMarkerWithNestedWith extends AdvancedRelationsTestMarker
{
    protected $with = ['post.owner'];
}
