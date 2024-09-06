<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Actions\Models\Action;
use Tests\Actions\TestCase;
use Actions\Core\ActionType;
use Tests\Actions\ModelWithAction;
use Actions\ActionsServiceProvider;
use Illuminate\Foundation\Auth\User;
use Tests\Actions\ModelWithoutAction;
use Actions\Core\Contracts\ActionRecorder;
use Actions\Models\Concerns\PerformsActions;
use Actions\Models\Contracts\ActionPerformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Actions\Models\Contracts\ModelActionTranslator;
use Illuminate\Translation\TranslationServiceProvider;

class ActionDescriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An action has a description
     *
     * @test
     */
    public function an_action_has_a_description(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);
        $model->delete();
        $model->restore();

        static::assertSame('Restored "Toby"', (string) $model->actions->get(0));
        static::assertSame('Deleted "Toby"', (string) $model->actions->get(1));
        static::assertSame('Updated "Toby"', (string) $model->actions->get(2));
        static::assertSame('Created "Larry"', (string) $model->actions->get(3));
    }

    /**
     * The subject name defaults to classname
     *
     * @test
     */
    public function the_subject_name_defaults_to_classname(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithoutAction::query()->forceCreate([
            'name' => 'Larry',
        ]);
        $this->app[ActionRecorder::class]->record($model);

        $model = $model->fresh();
        $model->update(['name' => 'Toby']);
        $this->app[ActionRecorder::class]->record($model);
        $model->delete();
        $this->app[ActionRecorder::class]->record($model);
        $model->restore();
        $this->app[ActionRecorder::class]->record($model);

        $actions = Action::query()->orderBy('id')->get();

        static::assertSame('Created "ModelWithoutAction"', (string) $actions->get(0));
        static::assertSame('Updated "ModelWithoutAction"', (string) $actions->get(1));
        static::assertSame('Deleted "ModelWithoutAction"', (string) $actions->get(2));
        static::assertSame('Restored "ModelWithoutAction"', (string) $actions->get(3));
    }

    /**
     * The performer can be included
     *
     * @test
     */
    public function the_performer_can_be_included(): void
    {
        $user = Performer::query()->forceCreate([
            'email' => 'l@r.ry',
            'name' => 'Larry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $model->update(['name' => 'Barry']);
        $model->delete();
        $model->restore();

        static::assertSame('"Barry" restored by Larry', (string) $model->actions->get(0));
        static::assertSame('"Barry" deleted by Larry', (string) $model->actions->get(1));
        static::assertSame('"Barry" updated by Larry', (string) $model->actions->get(2));
        static::assertSame('"Toby" created by Larry', (string) $model->actions->get(3));
    }

    /**
     * The detailed description has more information on the change
     *
     * @test
     */
    public function the_detailed_description_has_more_information_on_the_change(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);

        static::assertSame([[
            'description' => 'Added name',
            'before' => null,
            'after' => 'Larry',
        ]], $model->createAction->changes());
        static::assertSame([[
            'description' => 'Changed name',
            'before' => 'Larry',
            'after' => 'Toby',
        ]], $model->latestAction->changes());
    }

    /**
     * The description can be overridden by the subject model
     *
     * @test
     */
    public function the_description_can_be_overridden_by_the_subject_model(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var ModelWithDescription $model */
        $model = ModelWithDescription::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);

        static::assertSame([[
            'description' => 'Custom description',
            'before' => 'Hi',
            'after' => 'He',
        ]], $model->createAction->changes());
        static::assertSame('Yo yo', $model->createAction->description());
        static::assertSame([[
            'description' => 'Custom description',
            'before' => 'Hi',
            'after' => 'He',
        ]], $model->latestAction->changes());
        static::assertSame('Yo yo', $model->latestAction->description());
    }

    /**
     * The translations can be scoped to classname
     *
     * @test
     */
    public function the_translations_can_be_scoped_to_classname(): void
    {
        app()->instance('path.lang', __DIR__.'/../resources/lang');
        app()->register(TranslationServiceProvider::class, true);
        app('translator')->addNamespace('actions', __DIR__.'/../../lang');
        app()->register(ActionsServiceProvider::class, true);

        config(['actions.mandatory_performer' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);
        $model->delete();
        $model->restore();

        static::assertSame('Restored Model called "Toby"', (string) $model->actions->get(0));
        static::assertSame('Deleted Model called "Toby"', (string) $model->actions->get(1));
        static::assertSame('Updated Model called "Toby"', (string) $model->actions->get(2));
        static::assertSame('Created Model called "Larry"', (string) $model->actions->get(3));

        /** @var \Actions\Models\Action $update */
        $update = $model->actions->get(2);

        static::assertSame([[
            'description' => 'Changed name on Model',
            'before' => 'Larry',
            'after' => 'Toby',
        ]], $update->changes());
    }

    /**
     * A custom action can be used for certain action types
     *
     * @test
     */
    public function a_custom_action_can_be_used_for_certain_action_types(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var ModelWithDescription $model */
        $model = ModelWithDescription::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->delete();

        $action = $model->latestAction;

        static::assertSame([[
            'description' => 'Deleted',
        ]], $action->changes());
        static::assertSame('Custom delete message', $action->description());
    }
}

class Performer extends User implements ActionPerformer
{
    use PerformsActions;

    public string $performerDisplayNameKey = 'name';

    protected $table = 'users';
}

class ModelWithDescription extends ModelWithAction implements ModelActionTranslator
{
    protected static array $customActions = [
        ActionType::DELETE => CustomDeleteAction::class,
    ];

    public static function getActionDescription(Action $action, bool $withPerformer): string
    {
        return 'Yo yo';
    }

    public static function getActionChanges(Action $action): ?array
    {
        return [[
            'description' => 'Custom description',
            'before' => 'Hi',
            'after' => 'He',
        ]];
    }
}

class CustomDeleteAction extends Action
{
    public function description(bool $withPerformer = false): string
    {
        return 'Custom delete message';
    }

    public function changes(): ?array
    {
        return [[
            'description' => 'Deleted',
        ]];
    }
}
