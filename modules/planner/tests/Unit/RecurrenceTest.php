<?php

declare(strict_types=1);

namespace Tests\Planner\Unit;

use Tests\Planner\TestCase;
use Planner\Models\TodoList;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecurrenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recurring_todo_repeats_when_it_is_completed(): void
    {
        /** @var \Planner\Models\TodoList $list */
        $list = TodoList::query()->create(['name' => 'todos']);
        /** @var \Planner\Models\Todo $todo */
        $todo = $list->todos()->create([
            'name' => 'Do something',
            'due_by' => today()->addDay(),
            'recurrence' => 'FREQ=DAILY;COUNT=2',
        ]);

        $todo->complete();

        /** @var \Planner\Models\Todo $todo */
        $todo = $todo->fresh();

        static::assertFalse($todo->isComplete());
        static::assertTrue($todo->due_by?->is((string) today()->addDays(2)));

        $todo->complete();

        /** @var \Planner\Models\Todo $todo */
        $todo = $todo->fresh();

        static::assertFalse($todo->isComplete());
        static::assertTrue($todo->due_by?->is((string) today()->addDays(3)));

        $todo->complete();

        /** @var \Planner\Models\Todo $todo */
        $todo = $todo->fresh();

        static::assertTrue($todo->isComplete());
        static::assertNull($todo->recurrence);
    }
}
