<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use Illuminate\Support\Carbon;
use LaravelUtils\LaravelUtils;
use PHPUnit\Framework\TestCase;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class DatabaseEloquentSoftDeletingTest extends TestCase
{
    /**
     * Set up the database schema.
     */
    public function createSchema(): void
    {
        $this->schema()->create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * It does not change timestamps when soft deleting
     *
     * @test
     */
    public function it_does_not_change_timestamps_when_soft_deleting(): void
    {
        /** @var \Tests\LaravelUtils\Database\SoftDeleteDummyTest $firstModel */
        $firstModel = SoftDeleteDummyTest::query()->create(['name' => 'a']);

        /** @var \Tests\LaravelUtils\Database\SoftDeleteDummyTest $secondModel */
        $secondModel = SoftDeleteDummyTest::query()->create(['name' => 'b']);

        $firstUpdatedAt = $firstModel->updated_at;
        $secondUpdatedAt = $secondModel->updated_at;

        Carbon::setTestNow(now()->addMinutes(2));

        $firstModel->delete();

        LaravelUtils::disableTimestampsForSoftDelete();

        $secondModel->delete();

        static::assertTrue($firstModel->updated_at->greaterThan($firstUpdatedAt));
        static::assertTrue($secondModel->updated_at->equalTo($secondUpdatedAt));
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

        $eventDispatcher = Model::getEventDispatcher() ?: new Dispatcher;

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
        $this->schema()->drop('dummies');
    }
}

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SoftDeleteDummyTest extends Eloquent
{
    use SoftDeletes;

    protected $table = 'dummies';

    protected $guarded = [];
}
