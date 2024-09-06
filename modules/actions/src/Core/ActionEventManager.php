<?php

declare(strict_types=1);

namespace Actions\Core;

use Actions\Core\Contracts\ActionRecorder;
use Illuminate\Contracts\Config\Repository;
use Actions\Core\Contracts\ActionTranslator;
use Actions\Models\Observers\HasActionsObserver;
use Actions\Models\Observers\HasActionsCascadeObserver;
use Actions\Models\Observers\PerformsActionsCascadeObserver;
use Actions\Core\Contracts\ActionEventManager as ActionEventManagerInterface;

class ActionEventManager implements ActionEventManagerInterface
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

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    public function listenToModelEvents(string $model): void
    {
        /** @var \Illuminate\Contracts\Events\Dispatcher|null $dispatcher */
        $dispatcher = $model::getEventDispatcher();

        if (! $dispatcher) {
            return;
        }

        $model::observe(HasActionsObserver::class);

        $this->listenForCascades($model);
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    public function listenForCascades(string $model, bool $isSubject = true): void
    {
        $observer = $isSubject ? HasActionsCascadeObserver::class : PerformsActionsCascadeObserver::class;

        $model::observe($observer);
    }
}
