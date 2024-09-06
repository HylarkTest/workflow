<?php

declare(strict_types=1);

namespace Actions\Models\Observers;

use Actions\Models\Action;
use Actions\Core\NamePersistenceConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PerformsActionsCascadeObserver extends CascadeObserver
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    protected function actionRelation(Model $model): MorphMany
    {
        return $model->morphMany(Action::baseClass(), 'performer');
    }

    protected function nameColumn(): string
    {
        return 'performer_name';
    }

    protected function saveNameConfig(): NamePersistenceConfig
    {
        $config = $this->config->get('actions.save_performer_name');

        return $config instanceof NamePersistenceConfig ?
            $config :
            NamePersistenceConfig::from($config);
    }

    protected function translatedName(Model $model): string
    {
        return $this->translator->performerName($model) ?: '';
    }

    protected function nameWasChanged(Model $model): bool
    {
        return property_exists($model, 'performerDisplayNameKey')
            && $model->wasChanged($model->performerDisplayNameKey);
    }
}
