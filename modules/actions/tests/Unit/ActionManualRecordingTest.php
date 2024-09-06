<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Actions\Models\Action;
use Tests\Actions\TestCase;
use Tests\Actions\ModelWithoutAction;
use Actions\Core\Contracts\ActionRecorder;
use Actions\Models\Contracts\ActionLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionManualRecordingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An action can be recorded manually
     *
     * @test
     */
    public function an_action_can_be_recorded_manually(): void
    {
        $model = ModelWithoutAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->app[ActionRecorder::class]->record($model);

        static::assertSame(1, Action::query()->count());
        static::assertTrue(Action::query()->first()->subject->is($model));
    }

    /**
     * An action can be stopped
     *
     * @test
     */
    public function an_action_can_be_stopped(): void
    {
        $model = ModelThatStopsActions::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->app[ActionRecorder::class]->record($model);

        static::assertSame(0, Action::query()->count());
    }
}

class ModelThatStopsActions extends ModelWithoutAction implements ActionLimiter
{
    public function shouldRecordAction(?\Illuminate\Database\Eloquent\Model $performer, bool $force): bool
    {
        return false;
    }
}
