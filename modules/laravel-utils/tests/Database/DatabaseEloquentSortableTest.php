<?php

declare(strict_types=1);

namespace Tests\LaravelUtils\Database;

use PHPUnit\Framework\TestCase;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

class DatabaseEloquentSortableTest extends TestCase
{
    /**
     * Set up the database schema.
     */
    public function createSchema(): void
    {
        $this->schema()->create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('custom_column_sort');
            $table->integer('order');
            $table->softDeletes();
        });
    }

    /** @test */
    public function it_sets_the_order_column_on_creation(): void
    {
        foreach (SortableDummyTest::all() as $dummy) {
            static::assertSame($dummy->name, (string) $dummy->order);
        }
    }

    /** @test */
    public function it_can_get_the_highest_order_number(): void
    {
        static::assertSame(SortableDummyTest::query()->count(), (new SortableDummyTest)->getHighestOrderNumber());
    }

    /** @test */
    public function it_can_get_the_highest_order_number_with_trashed_models(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTestWithSoftDeletes $dummy */
        $dummy = SortableDummyTestWithSoftDeletes::query()->first();
        $dummy->delete();

        static::assertSame(SortableDummyTestWithSoftDeletes::withTrashed()->count(), (new SortableDummyTestWithSoftDeletes)->getHighestOrderNumber());
    }

    /** @test */
    public function it_can_set_a_new_order(): void
    {
        $newOrder = Collection::make(SortableDummyTest::all()->pluck('id'))->shuffle();

        SortableDummyTest::setNewOrder($newOrder);

        foreach (SortableDummyTest::query()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column(): void
    {
        $newOrder = Collection::make(SortableDummyTest::all()->pluck('custom_column_sort'))->shuffle();

        SortableDummyTest::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (SortableDummyTest::query()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_with_trashed_models(): void
    {
        $dummies = SortableDummyTestWithSoftDeletes::all();

        $dummies->random()->delete();

        $newOrder = Collection::make($dummies->pluck('id'))->shuffle();

        SortableDummyTestWithSoftDeletes::setNewOrder($newOrder);

        foreach (SortableDummyTestWithSoftDeletes::withTrashed()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column_with_trashed_models(): void
    {
        $dummies = SortableDummyTestWithSoftDeletes::all();

        $dummies->random()->delete();

        $newOrder = Collection::make($dummies->pluck('custom_column_sort'))->shuffle();

        SortableDummyTestWithSoftDeletes::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (SortableDummyTestWithSoftDeletes::withTrashed()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_without_trashed_models(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTestWithSoftDeletes $dummy */
        $dummy = SortableDummyTestWithSoftDeletes::query()->first();
        $dummy->delete();

        $newOrder = Collection::make(SortableDummyTestWithSoftDeletes::query()->pluck('id'))->shuffle();

        SortableDummyTestWithSoftDeletes::setNewOrder($newOrder);

        foreach (SortableDummyTestWithSoftDeletes::query()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column_without_trashed_models(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTestWithSoftDeletes $dummy */
        $dummy = SortableDummyTestWithSoftDeletes::query()->first();
        $dummy->delete();

        $newOrder = Collection::make(SortableDummyTestWithSoftDeletes::query()->pluck('custom_column_sort'))->shuffle();

        SortableDummyTestWithSoftDeletes::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (SortableDummyTestWithSoftDeletes::query()->orderBy('order')->get() as $i => $dummy) {
            static::assertSame($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_will_respect_the_sort_when_creating_setting(): void
    {
        $model = new class extends SortableDummyTest {};

        static::assertTrue($model->shouldSortWhenCreating());

        $model = new class extends SortableDummyTest
        {
            protected bool $shouldSortWhenCreating = false;
        };
        static::assertFalse($model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_provides_an_ordered_trait(): void
    {
        $i = 1;

        foreach (SortableDummyTest::query()->get()->pluck('order') as $order) {
            static::assertSame($i++, $order);
        }
    }

    /** @test */
    public function it_can_move_the_order_down(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $firstModel */
        $firstModel = SortableDummyTest::query()->find(3);
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $secondModel */
        $secondModel = SortableDummyTest::query()->find(4);

        static::assertSame(3, $firstModel->order);
        static::assertSame(4, $secondModel->order);

        static::assertNotFalse($firstModel->moveOrderDown());

        $firstModel = $firstModel->fresh();
        $secondModel = $secondModel->fresh();

        static::assertSame(4, $firstModel?->order);
        static::assertSame(3, $secondModel?->order);
    }

    /** @test */
    public function it_will_not_fail_when_it_cant_move_the_order_down(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $lastModel */
        $lastModel = SortableDummyTest::query()->latest('id')->first();

        static::assertSame(20, $lastModel->order);
        static::assertSame($lastModel, $lastModel->moveOrderDown());
    }

    /** @test */
    public function it_can_move_the_order_up(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $firstModel */
        $firstModel = SortableDummyTest::query()->find(3);
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $secondModel */
        $secondModel = SortableDummyTest::query()->find(4);

        static::assertSame(3, $firstModel->order);
        static::assertSame(4, $secondModel->order);

        static::assertNotFalse($secondModel->moveOrderUp());

        $firstModel = $firstModel->fresh();
        $secondModel = $secondModel->fresh();

        static::assertSame(4, $firstModel?->order);
        static::assertSame(3, $secondModel?->order);
    }

    /** @test */
    public function it_will_not_break_when_it_cant_move_the_order_up(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $lastModel */
        $lastModel = SortableDummyTest::query()->first();

        static::assertSame(1, $lastModel->order);
        static::assertSame($lastModel, $lastModel->moveOrderUp());
    }

    /** @test */
    public function it_can_swap_the_position_of_two_given_models(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $firstModel */
        $firstModel = SortableDummyTest::query()->find(3);
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $secondModel */
        $secondModel = SortableDummyTest::query()->find(4);

        static::assertSame(3, $firstModel->order);
        static::assertSame(4, $secondModel->order);

        SortableDummyTest::swapOrder($firstModel, $secondModel);

        static::assertSame(4, $firstModel->order);
        static::assertSame(3, $secondModel->order);
    }

    /** @test */
    public function it_can_swap_itself_with_another_model(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $firstModel */
        $firstModel = SortableDummyTest::query()->find(3);
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $secondModel */
        $secondModel = SortableDummyTest::query()->find(4);

        static::assertSame(3, $firstModel->order);
        static::assertSame(4, $secondModel->order);

        $firstModel->swapOrderWithModel($secondModel);

        static::assertSame(4, $firstModel->order);
        static::assertSame(3, $secondModel->order);
    }

    /** @test */
    public function it_can_place_a_model_below_another_model_lower_down(): void
    {
        $startPosition = 3;

        $moveBelowPosition = 8;

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($startPosition);

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $referenceModel */
        $referenceModel = SortableDummyTest::query()->find($moveBelowPosition);

        static::assertSame(3, $model->order);

        $model = $model->moveBelow($referenceModel);

        static::assertSame(8, $model->order);
        static::assertSame(7, $referenceModel->fresh()?->order);

        $i = 1;

        foreach (SortableDummyTest::query()->get()->pluck('order') as $order) {
            static::assertSame($i++, $order);
        }
    }

    /** @test */
    public function it_can_place_a_model_below_another_model_higher_up(): void
    {
        $startPosition = 8;

        $moveBelowPosition = 3;

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($startPosition);

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $referenceModel */
        $referenceModel = SortableDummyTest::query()->find($moveBelowPosition);

        static::assertSame(8, $model->order);

        $model = $model->moveBelow($referenceModel);

        static::assertSame(4, $model->order);
        static::assertSame(3, $referenceModel->fresh()?->order);

        $i = 1;

        foreach (SortableDummyTest::query()->get()->pluck('order') as $order) {
            static::assertSame($i++, $order);
        }
    }

    /** @test */
    public function it_can_place_a_model_above_another_model_lower_down(): void
    {
        $startPosition = 3;

        $moveAbovePosition = 8;

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($startPosition);

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $referenceModel */
        $referenceModel = SortableDummyTest::query()->find($moveAbovePosition);

        static::assertSame(3, $model->order);

        $model = $model->moveAbove($referenceModel);

        static::assertSame(7, $model->order);
        static::assertSame(8, $referenceModel->fresh()?->order);

        $i = 1;

        foreach (SortableDummyTest::query()->get()->pluck('order') as $order) {
            static::assertSame($i++, $order);
        }
    }

    /** @test */
    public function it_can_place_a_model_above_another_model_higher_up(): void
    {
        $startPosition = 8;

        $moveAbovePosition = 3;

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($startPosition);

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $referenceModel */
        $referenceModel = SortableDummyTest::query()->find($moveAbovePosition);

        static::assertSame(8, $model->order);

        $model = $model->moveAbove($referenceModel);

        static::assertSame(3, $model->order);
        static::assertSame(4, $referenceModel->fresh()?->order);

        $i = 1;

        foreach (SortableDummyTest::get()->pluck('order') as $order) {
            static::assertSame($i++, $order);
        }
    }

    /** @test */
    public function it_can_move_a_model_to_the_first_place(): void
    {
        $position = 3;

        $oldModels = SortableDummyTest::query()->ordered()->get();

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($position);

        static::assertSame(3, $model->order);

        $model = $model->moveToStart();

        static::assertSame(1, $model->order);

        $oldModels = $oldModels->pluck('id')->all();
        $newModels = SortableDummyTest::query()->ordered()->pluck('id')->all();

        static::assertSame($oldModels[2], $newModels[0]);

        array_splice($oldModels, 2, 1);
        array_splice($newModels, 0, 1);

        static::assertSame($oldModels, $newModels);
    }

    /**
     * @test
     */
    public function it_can_move_a_model_to_the_last_place(): void
    {
        $position = 3;

        $oldModels = SortableDummyTest::query()->whereKeyNot($position)->get();

        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->find($position);

        static::assertNotSame(20, $model->order);

        $model = $model->moveToEnd();

        static::assertSame(20, $model->order);

        $oldModels = $oldModels->pluck('order', 'id');

        $newModels = SortableDummyTest::query()->whereKeyNot($position)->get()->pluck('order', 'id');

        foreach ($oldModels as $key => $order) {
            if ($order > $position) {
                static::assertSame($order - 1, $newModels[$key]);
            } else {
                static::assertSame($order, $newModels[$key]);
            }
        }
    }

    /** @test */
    public function it_can_tell_if_element_is_first_in_order(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<\LaravelUtils\Database\Eloquent\Contracts\Sortable> $model */
        $model = (new SortableDummyTest)->buildSortQuery()->get();
        static::assertTrue($model[0]?->isFirstInOrder());
        static::assertFalse($model[1]?->isFirstInOrder());
    }

    /** @test */
    public function it_can_tell_if_element_is_last_in_order(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<\LaravelUtils\Database\Eloquent\Contracts\Sortable> $model */
        $model = (new SortableDummyTest)->buildSortQuery()->get();
        static::assertTrue($model[$model->count() - 1]?->isLastInOrder());
        static::assertFalse($model[$model->count() - 2]?->isLastInOrder());
    }

    /**
     * It can be automatically ordered
     *
     * @test
     */
    public function it_can_be_automatically_ordered(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->first();
        $model->moveToEnd();

        static::assertTrue($model->is(SortableDummyTest::all()->last()));
    }

    /** @test */
    public function automatic_ordering_can_be_disabled(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableUnsortedDummyTest $model */
        $model = SortableUnsortedDummyTest::query()->first();
        $model->moveToEnd();

        static::assertFalse($model->is(SortableUnsortedDummyTest::all()->last()));
    }

    /**
     * Automatic ordering is disabled if another order is used
     *
     * @test
     */
    public function automatic_ordering_is_disabled_if_another_order_is_used(): void
    {
        /** @var \Tests\LaravelUtils\Database\SortableDummyTest $model */
        $model = SortableDummyTest::query()->first();
        $model->moveToEnd();
        static::assertTrue($model->is(SortableDummyTest::query()->oldest('id')->first()));
    }

    /**
     * Helpers...
     */
    protected function seedData(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            SortableDummyTest::query()->create([
                'name' => $i,
                'custom_column_sort' => mt_rand(),
            ]);
        }
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

        $this->seedData();
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
 * @property string $custom_column_sort
 * @property int $order
 */
class SortableDummyTest extends Eloquent implements Sortable
{
    use IsSortable;

    public $timestamps = false;

    protected $table = 'dummies';

    protected $guarded = [];

    protected $casts = ['order' => 'int'];
}

class SortableDummyTestWithSoftDeletes extends SortableDummyTest
{
    use SoftDeletes;
}

class SortableUnsortedDummyTest extends SortableDummyTest
{
    protected static bool $sortIfNotExplicitlySorting = false;
}
