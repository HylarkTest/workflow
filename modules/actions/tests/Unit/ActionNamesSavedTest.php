<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Actions\Models\Action;
use Tests\Actions\TestCase;
use Tests\Actions\ModelWithAction;
use Illuminate\Foundation\Auth\User;
use Tests\Actions\ModelWithoutAction;
use Actions\Core\NamePersistenceConfig;
use Actions\Core\Contracts\ActionRecorder;
use Actions\Models\Concerns\PerformsActions;
use Actions\Models\Contracts\ActionPerformer;
use Actions\Models\Contracts\SoftDeleteModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionNamesSavedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An action subject name can be unsaved
     *
     * @test
     */
    public function an_action_subject_name_can_be_unsaved(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::NEVER]);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->assertSubjectName('Larry', $model->createAction);
        static::assertNull($model->createAction->subject_name);
    }

    /**
     * The performer name can be unsaved
     *
     * @test
     */
    public function the_performer_name_can_be_unsaved(): void
    {
        config(['actions.save_performer_name' => NamePersistenceConfig::NEVER]);
        $user = PerformerWithName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);
        $this->be($user);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertNull($model->createAction->performer_name);
    }

    /**
     * An action name can be saved on each action
     *
     * @test
     */
    public function an_action_name_can_be_saved_on_each_action(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::ALWAYS]);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);

        $this->assertSubjectName('Larry', $model->createAction);
        static::assertSame('Larry', $model->createAction->subject_name);
        $this->assertSubjectName('Toby', $model->latestAction);
        static::assertSame('Toby', $model->latestAction->subject_name);
    }

    /**
     * The performer name can be saved on each action
     *
     * @test
     */
    public function the_performer_name_can_be_saved_on_each_action(): void
    {
        config(['actions.save_performer_name' => NamePersistenceConfig::ALWAYS]);

        $user = PerformerWithName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $model->update(['name' => 'Gary']);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertSame('Larry', $model->createAction->performer_name);
        $this->assertPerformerName('Larry', $model->latestAction);
        static::assertSame('Larry', $model->latestAction->performer_name);
    }

    /**
     * An action name can be saved on delete
     *
     * @test
     */
    public function an_action_name_can_be_saved_on_delete(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::ON_DELETE]);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);
        $this->assertSubjectName('Toby', $model->createAction);
        static::assertNull($model->createAction->subject_name);

        $model = $model->fresh();

        $model->forceDelete();

        static::assertSame('Toby', $model->createAction->subject_name);
        $this->assertSubjectName('Toby', $model->createAction);
        static::assertSame('Toby', $model->latestAction->subject_name);
        $this->assertSubjectName('Toby', $model->latestAction);
    }

    /**
     * The performer name can be saved on delete
     *
     * @test
     */
    public function the_performer_name_can_be_saved_on_delete(): void
    {
        config(['actions.save_performer_name' => NamePersistenceConfig::ON_DELETE]);

        $user = PerformerWithName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertNull($model->createAction->performer_name);

        $model = $model->fresh();

        $user->forceDelete();

        static::assertSame('Larry', $model->createAction->performer_name);
        $this->assertPerformerName('Larry', $model->createAction);
    }

    /**
     * An action name is not saved if the trait is not used
     *
     * @test
     */
    public function an_action_name_is_not_saved_if_the_trait_is_not_used(): void
    {
        config([
            'actions.save_subject_name' => NamePersistenceConfig::ON_DELETE,
            'actions.mandatory_performer' => false,
        ]);
        /** @var ModelWithoutAction $model */
        $model = ModelWithoutAction::query()->forceCreate([
            'name' => 'Larry',
        ]);
        $this->app[ActionRecorder::class]->recordEvent('created', $model);

        /** @var \Actions\Models\Action $action */
        $action = Action::query()->first();
        $this->assertSubjectName('ModelWithoutAction', $action);
        static::assertNull($action->subject_name);
    }

    /**
     * The performer name is not saved if the trait is not used
     *
     * @test
     */
    public function the_performer_name_is_not_saved_if_the_trait_is_not_used(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::ON_DELETE]);
        $user = PerformerWithoutTrait::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);
        $this->be($user);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertNull($model->createAction->performer_name);
    }

    /**
     * An action name can be saved on update
     *
     * @test
     */
    public function an_action_name_can_be_saved_on_update(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::ON_UPDATE]);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->assertSubjectName('Larry', $model->createAction);
        static::assertSame('Larry', $model->createAction->subject_name);

        $model->update(['name' => 'Toby']);
        $model = $model->fresh();
        $this->assertSubjectName('Toby', $model->latestAction);
        static::assertSame('Toby', $model->latestAction->subject_name);

        $model = $model->fresh();

        $model->forceDelete();

        static::assertSame('Toby', $model->createAction->subject_name);
        $this->assertSubjectName('Toby', $model->createAction);
        static::assertSame('Toby', $model->latestAction->subject_name);
        $this->assertSubjectName('Toby', $model->latestAction);
    }

    /**
     * The performer name can be saved on update
     *
     * @test
     */
    public function the_performer_name_can_be_saved_on_update(): void
    {
        config(['actions.save_performer_name' => NamePersistenceConfig::ON_UPDATE]);

        $user = PerformerWithName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertSame('Larry', $model->createAction->performer_name);

        $user->forceFill(['name' => 'Toby'])->save();
        $model = $model->fresh();
        $this->assertPerformerName('Toby', $model->latestAction);
        static::assertSame('Toby', $model->latestAction->performer_name);

        $model = $model->fresh();

        $user->forceDelete();

        static::assertSame('Toby', $model->createAction->performer_name);
        $this->assertPerformerName('Toby', $model->createAction);
        static::assertSame('Toby', $model->latestAction->performer_name);
        $this->assertPerformerName('Toby', $model->latestAction);
    }

    /**
     * Action name can be saved on soft delete
     *
     * @test
     */
    public function action_name_can_be_saved_on_soft_delete(): void
    {
        config(['actions.save_subject_name' => NamePersistenceConfig::ON_SOFT_DELETE]);
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);
        $this->assertSubjectName('Toby', $model->createAction);
        static::assertNull($model->createAction->subject_name);

        $model = $model->fresh();

        $model->delete();

        static::assertSame('Toby', $model->createAction->subject_name);
        $this->assertSubjectName('Toby', $model->createAction);

        $model = $model->fresh();

        $model->restore();

        $this->assertSubjectName('Toby', $model->createAction);
        static::assertNull($model->createAction->subject_name);

        $model = $model->fresh();

        $model->forceDelete();

        static::assertSame('Toby', $model->createAction->subject_name);
        $this->assertSubjectName('Toby', $model->createAction);
    }

    /**
     * The performer name can be saved on soft delete
     *
     * @test
     */
    public function the_performer_name_can_be_saved_on_soft_delete(): void
    {
        config(['actions.save_performer_name' => NamePersistenceConfig::ON_SOFT_DELETE]);

        $user = PerformerWithName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertNull($model->createAction->performer_name);

        $model = $model->fresh();

        $user->delete();

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertSame('Larry', $model->createAction->performer_name);

        $model = $model->fresh();

        $user->restore();

        $this->assertPerformerName('Larry', $model->createAction);
        static::assertNull($model->createAction->performer_name);

        $model = $model->fresh();

        $user->forceDelete();

        static::assertSame('Larry', $model->createAction->performer_name);
        $this->assertPerformerName('Larry', $model->createAction);
    }

    /**
     * The subject name can be overridden
     *
     * @test
     */
    public function the_subject_name_can_be_overridden(): void
    {
        /** @var \Tests\Actions\Unit\ModelWithName $model */
        $model = ModelWithName::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->update(['name' => 'Toby']);

        $this->assertSubjectName('Gary', $model->createAction);
        $this->assertSubjectName('Gary', $model->latestAction);
    }

    /**
     * The performer name can be overridden
     *
     * @test
     */
    public function the_performer_name_can_be_overridden(): void
    {
        $user = PerformerWithNewName::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\Unit\ModelWithName $model */
        $model = ModelWithName::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->assertPerformerName('Gary', $model->createAction);
    }
}

class ModelWithName extends ModelWithAction
{
    public function getActionSubjectName(): ?string
    {
        return 'Gary';
    }
}

class PerformerWithName extends User implements ActionPerformer, SoftDeleteModel
{
    use PerformsActions;
    use SoftDeletes;

    public string $performerDisplayNameKey = 'name';

    protected $table = 'users';
}

class PerformerWithNewName extends PerformerWithName
{
    public function getActionPerformerName(): ?string
    {
        return 'Gary';
    }
}

class PerformerWithoutTrait extends User implements ActionPerformer
{
    protected $table = 'users';

    public function getActionPerformerName(): ?string
    {
        return 'Larry';
    }
}
