<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LaravelUtils\Database\Eloquent\Concerns\HasAdvancedRelationships;

class DatabaseEloquentPivotLoadingTest extends TestCase
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
            $table->foreignIdFor(PivotLoadingTestUser::class);
            $table->foreignIdFor(PivotLoadingTestPost::class);
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
            $table->foreignIdFor(PivotLoadingTestMarker::class);
            $table->timestamps();
        });
    }

    /**
     * Pivot models can have relationships
     *
     * @test
     */
    public function pivot_models_can_have_relationships(): void
    {
        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestUser $user */
        $user = PivotLoadingTestUser::query()->create(['email' => 'me@example.com']);
        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestPost $post */
        $post = PivotLoadingTestPost::query()->create(['text' => 'A post']);
        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestMarker $tag */
        $tag = PivotLoadingTestMarker::query()->create(['text' => 'A tag']);

        $user->posts()->attach($post->id);

        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestPost $foundPost */
        $foundPost = $user->posts->first();
        $foundPost->getAttribute('pivot')->tags()->attach($tag->id);

        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestUser $foundUser */
        $foundUser = $post->users->first();
        static::assertTrue($foundUser->getAttribute('pivot')->tags->first()->is($tag));
    }

    /**
     * Pivot relationships can be eager loaded
     *
     * TODO: Allow loading relationships on pivot models
     *
     * @test
     */
    //    public function pivot_relationships_can_be_eager_loaded(): void
    //    {
    //        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestUser $user */
    //        $user = PivotLoadingTestUser::query()->create(['email' => 'me@example.com']);
    //        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestPost $post */
    //        $post = PivotLoadingTestPost::query()->create(['text' => 'A post']);
    //        /** @var \Tests\LaravelUtils\Database\PivotLoadingTestTag $tag */
    //        $tag = PivotLoadingTestTag::query()->create(['text' => 'A tag']);
    //
    //        $user->posts()->attach($post->id);
    //
    //        $user->posts->first()->pivot->tags()->attach($tag->id);
    //
    //        $user = $user->fresh();
    //        $user->load([
    //            'posts' => fn(EloquentBuilder $query) => $query->loadPivotRelations('tags'),
    //        ]);
    //        static::assertTrue($user->relationLoaded('posts'));
    //        static::assertTrue($user->posts->first()->pivot->relationLoaded('tags'));
    //    }

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
        $this->schema()->drop('tags');
        $this->schema()->drop('taggables');
    }
}
/**
 * Eloquent Models...
 *
 * @property int $id
 * @property \Illuminate\Database\Eloquent\Collection<\Tests\LaravelUtils\Database\PivotLoadingTestPost> $posts
 */
class PivotLoadingTestUser extends Eloquent
{
    use HasAdvancedRelationships;

    protected $table = 'users';

    protected $guarded = [];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(PivotLoadingTestPost::class, 'posts_users')
            ->using(PivotLoadingPivotLoadingTestMarkerablePivot::class)
            ->withPivot('id');
    }
}

/**
 * Eloquent Models...
 *
 * @property int $id
 * @property \Illuminate\Database\Eloquent\Collection<\Tests\LaravelUtils\Database\PivotLoadingTestUser> $users
 */
class PivotLoadingTestPost extends Eloquent
{
    protected $table = 'posts';

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(PivotLoadingTestUser::class, 'posts_users')
            ->using(PivotLoadingPivotLoadingTestMarkerablePivot::class)
            ->withPivot('id');
    }
}

/**
 * @property int $id
 */
class PivotLoadingTestMarker extends Eloquent
{
    protected $table = 'tags';

    protected $guarded = [];
}

class PivotLoadingPivotLoadingTestMarkerablePivot extends Pivot
{
    public $incrementing = true;

    protected $table = 'posts_users';

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(PivotLoadingTestMarker::class, 'taggable');
    }
}
