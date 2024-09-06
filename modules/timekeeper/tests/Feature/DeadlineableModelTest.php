<?php

declare(strict_types=1);

namespace Tests\Timekeeper\Feature;

use Tests\Timekeeper\TestCase;
use Timekeeper\Models\Deadline;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class DeadlineableModelTest extends TestCase
{
    /**
     * A model can have deadlines
     *
     * @test
     */
    public function a_model_can_have_deadlines(): void
    {
        $deadline = Deadline::query()->forceCreate(['name' => 'Deadline']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);

        $item->deadlines()->attach($deadline);

        static::assertCount(1, $item->deadlines);
        static::assertTrue($item->deadlines->first()->is($deadline));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('items', static function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('itemables', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('related_id');
            $table->unsignedInteger('foreign_id');
        });
    }
}

use Timekeeper\Models\DeadlineableModel;
use Illuminate\Database\Schema\Blueprint;
use Timekeeper\Models\Concerns\HasDeadlines;

class Item extends Model implements DeadlineableModel
{
    use HasDeadlines;
}
