<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use Illuminate\Events\Dispatcher;
use Orchestra\Testbench\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Jobs\SoftDeleteCascadeJob;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

class DatabaseEloquentAdvancedSoftDeletingTest extends TestCase
{
    /* TESTS */

    /** @test */
    public function children_get_soft_deleted_when_parent_soft_deleted(): void
    {
        $user = $this->createUser();

        foreach (array_keys($this->getRelationships()) as $relationship) {
            static::assertSame(1, $user->$relationship()->count());
        }

        $user->delete();

        foreach ($this->getRelationships() as $relationship => $class) {
            static::assertEmpty($user->$relationship);
            static::assertSame(1, $class::query()->withTrashed()->whereNotNull('deleted_at')->count());
        }
    }

    /** @test */
    public function children_get_force_deleted_when_parent_force_deleted(): void
    {
        $user = $this->createUser();

        foreach (array_keys($this->getRelationships()) as $relationship) {
            static::assertSame(1, $user->$relationship()->count());
        }

        $user->forceDelete();

        foreach ($this->getRelationships() as $relationship => $class) {
            static::assertEmpty($user->$relationship);
            static::assertSame(0, $class::query()->withTrashed()->count());
        }
    }

    /** @test */
    public function when_queue_option_is_set_child_deletions_are_queued(): void
    {
        Queue::fake();
        $user = $this->createUser();

        foreach (array_keys($this->getRelationships()) as $relationship) {
            static::assertSame(1, $user->$relationship()->count());
        }

        $queuedJobs = 0;
        $user->delete();

        Queue::assertPushed(SoftDeleteCascadeJob::class, function (SoftDeleteCascadeJob $job) use (&$queuedJobs) {
            if ($job->connection === null) {
                $queuedJobs++;
            }

            return true;
        });
        static::assertSame(2, $queuedJobs);
    }

    /** @test */
    public function when_queue_option_is_not_set_child_deletions_are_synchronous(): void
    {
        Queue::fake();
        $user = $this->createUser();

        foreach (array_keys($this->getRelationships()) as $relationship) {
            static::assertSame(1, $user->$relationship()->count());
        }

        $syncedJobs = 0;
        $user->delete();

        Queue::assertPushed(SoftDeleteCascadeJob::class, function (SoftDeleteCascadeJob $job) use (&$syncedJobs) {
            if ($job->connection === 'sync') {
                $syncedJobs++;
            }

            return true;
        });
        static::assertSame(1, $syncedJobs);
    }

    /* HELPERS */

    /**
     * Get a schema builder instance.
     */
    protected function schema(): Builder
    {
        return $this->connection()->getSchemaBuilder();
    }

    /**
     * Get a database connection instance.
     */
    protected function connection(): Connection
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = Model::getConnectionResolver()->connection();

        return $connection;
    }

    /**
     * Get the relationships to the user
     */
    protected function getRelationships(): array
    {
        return [
            'posts' => AdvancedSoftDeletesTestPost::class,
            'comments' => AdvancedSoftDeletesTestComment::class,
            'lists' => AdvancedSoftDeletesTestList::class,
        ];
    }

    /**
     * Create a user with posts, comments and lists.
     */
    protected function createUser(): AdvancedSoftDeletesTestUser
    {
        $user = AdvancedSoftDeletesTestUser::create([
            'email' => 'user@email.com',
        ]);

        foreach (array_keys($this->getRelationships()) as $relationship) {
            $user->$relationship()->create(['text' => 'child']);
        }

        return $user;
    }

    /**
     * Set up the database schema.
     */
    protected function createSchema(): void
    {
        $this->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->morphs('postable');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->schema()->create('lists', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->morphs('listable');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->morphs('commentable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /* SETUP & TEAR DOWN */

    protected function setUp(): void
    {
        parent::setUp();

        $db = new DB;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $eventDispatcher = Model::getEventDispatcher() ?: new Dispatcher;

        $db->setEventDispatcher($eventDispatcher);
        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();

        app('config')->set('app.delete_cascade_queue', 'slow');
    }

    /**
     * Tear down the database schema.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('users');
        $this->schema()->drop('posts');
        $this->schema()->drop('lists');
        $this->schema()->drop('comments');

        parent::tearDown();
    }
}

/* MODELS */

class AdvancedSoftDeletesTestUser extends Model
{
    use AdvancedSoftDeletes;

    protected $table = 'users';

    protected $guarded = [];

    protected array $deleteCascadeRelationships = [
        'posts',
        'lists' => 'queue',
        'comments' => ['queue'],
    ];

    public function posts(): MorphMany
    {
        return $this->morphMany(AdvancedSoftDeletesTestPost::class, 'postable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(AdvancedSoftDeletesTestComment::class, 'commentable');
    }

    public function lists(): MorphMany
    {
        return $this->morphMany(AdvancedSoftDeletesTestList::class, 'listable');
    }
}

class AdvancedSoftDeletesTestPost extends Model
{
    use SoftDeletes;

    protected $table = 'posts';

    protected $guarded = [];
}

class AdvancedSoftDeletesTestList extends Model
{
    use SoftDeletes;

    protected $table = 'lists';

    protected $guarded = [];
}

class AdvancedSoftDeletesTestComment extends Model
{
    use SoftDeletes;

    protected $table = 'comments';

    protected $guarded = [];
}
