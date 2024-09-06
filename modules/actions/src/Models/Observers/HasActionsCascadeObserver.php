<?php

declare(strict_types=1);

namespace Actions\Models\Observers;

use Actions\Models\Action;
use Actions\Core\NamePersistenceConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Actions\Models\Contracts\ActionSubjectNameProvider;

class HasActionsCascadeObserver extends CascadeObserver
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    protected function actionRelation(Model $model): MorphMany
    {
        return $model->morphMany(Action::baseClass(), 'subject');
    }

    protected function nameColumn(): string
    {
        return 'subject_name';
    }

    protected function saveNameConfig(): NamePersistenceConfig
    {
        $config = $this->config->get('actions.save_subject_name');

        return $config instanceof NamePersistenceConfig ?
            $config :
            NamePersistenceConfig::from($config);
    }

    protected function translatedName(Model $model): string
    {
        return $this->translator->subjectName($model);
    }

    protected function nameWasChanged(Model $model): bool
    {
        if ($model instanceof ActionSubjectNameProvider) {
            return $model->subjectNameWasChanged();
        }
        $nameKey = property_exists($this, 'subjectDisplayNameKey') ? $this->subjectDisplayNameKey : 'name';

        return $model->wasChanged($nameKey);
    }
}
