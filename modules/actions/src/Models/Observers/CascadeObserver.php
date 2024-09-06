<?php

declare(strict_types=1);

namespace Actions\Models\Observers;

use Actions\Core\NamePersistenceConfig;
use Illuminate\Database\Eloquent\Model;
use Actions\Core\Contracts\ActionRecorder;
use Illuminate\Contracts\Config\Repository;
use Actions\Core\Contracts\ActionTranslator;
use Actions\Models\Contracts\SoftDeleteModel;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

abstract class CascadeObserver
{
    public Repository $config;

    protected ActionTranslator $translator;

    protected ActionRecorder $defaultRecorder;

    public function __construct(Repository $config, ActionTranslator $translator, ActionRecorder $recorder)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->defaultRecorder = $recorder;
    }

    public function deleted(Model $model): void
    {
        $saveNameConfig = $this->saveNameConfig();
        if (
            $saveNameConfig->isDeleteOrSoftDelete()
            && $this->shouldCascade($saveNameConfig, $model)
        ) {
            $this->actionRelation($model)->update(
                [$this->nameColumn() => $this->translatedName($model)]
            );
        }
    }

    public function restored(Model $model): void
    {
        $saveNameConfig = $this->saveNameConfig();

        if ($saveNameConfig->isOnSoftDelete()) {
            $this->actionRelation($model)->update(
                [$this->nameColumn() => null]
            );
        }
    }

    public function updated(Model $model): void
    {
        $saveNameConfig = $this->saveNameConfig();

        if ($saveNameConfig->isOnUpdate() && $this->nameWasChanged($model)) {
            $this->actionRelation($model)->update(
                [$this->nameColumn() => $this->translatedName($model)]
            );
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Actions\Models\Action>|\Illuminate\Database\Eloquent\Relations\HasOneOrMany<\Actions\Models\Action>
     */
    abstract protected function actionRelation(Model $model): BelongsToMany|HasOneOrMany;

    abstract protected function nameColumn(): string;

    abstract protected function saveNameConfig(): NamePersistenceConfig;

    abstract protected function translatedName(Model $model): string;

    abstract protected function nameWasChanged(Model $model): bool;

    protected function shouldCascade(NamePersistenceConfig $saveNameConfig, Model $model): bool
    {
        /*
         * If the model should cascade on delete and the model still exists, it
         * hasn't been deleted, so we should not cascade.
         */
        if ($model->exists && $saveNameConfig->isOnDelete()) {
            return false;
        }

        if ($saveNameConfig->isOnSoftDelete()) {
            /*
             * If the model should cascade on soft delete, and it doesn't use
             * SoftDeletes then we should not cascade.
             */
            if (! ($model instanceof SoftDeleteModel)) {
                return false;
            }
            /*
             * If the model does use SoftDeletes but the deleted_at column is
             * null then it is not being soft deleted, so we should not cascade.
             */
            if ($model->exists && $model->{$model->getDeletedAtColumn()} === null) {
                return false;
            }
        }

        return true;
    }
}
