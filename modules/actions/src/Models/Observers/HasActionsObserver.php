<?php

declare(strict_types=1);

namespace Actions\Models\Observers;

use Sentry\State\Scope;
use Actions\Models\Action;

use function Sentry\configureScope;

use Illuminate\Database\Eloquent\Model;
use Actions\Core\Contracts\ActionRecorder;
use Illuminate\Contracts\Config\Repository;
use Actions\Models\Contracts\ActionRecorderProvider;

class HasActionsObserver
{
    protected Repository $config;

    protected ActionRecorder $defaultRecorder;

    public function __construct(Repository $config, ActionRecorder $recorder)
    {
        $this->config = $config;
        $this->defaultRecorder = $recorder;
    }

    public function created(Model $subject): void
    {
        $this->recordEvent($subject, 'created');
    }

    public function updated(Model $subject): void
    {
        /*
         * If the model is restored it gets saved first and we don't
         * want to record that event, we just want to record the
         * `restored` event straight afterwards.
         */
        if (! $subject->wasChanged('deleted_at')) {
            $this->recordEvent($subject, 'updated');
        }
    }

    public function deleted(Model $subject): void
    {
        if (! $subject->exists && $this->config->get('actions.cascade')) {
            $class = Action::baseClass();
            $subject->morphMany($class, 'subject')->delete();
        } else {
            $this->recordEvent($subject, 'deleted');
        }
    }

    public function restored(Model $subject): void
    {
        $this->recordEvent($subject, 'restored');
    }

    protected function recordEvent(Model $subject, string $event): void
    {
        if (! $this->config->get('actions.automatic')) {
            return;
        }
        if ($subject instanceof ActionRecorderProvider) {
            $recorder = $subject::getActionRecorder() ?: $this->defaultRecorder;
        } else {
            $recorder = $this->defaultRecorder;
        }

        try {
            $recorder->recordEvent($event, $subject);
        } catch (\Throwable $e) {
            // Don't let an error in the actions code prevent the main application from working
            configureScope(fn (Scope $scope) => $scope->setExtras([
                'subject' => $subject->getKey(),
                'subject_class' => $subject::class,
                'event' => $event,
                'attributes' => $subject->getAttributes(),
                'changes' => $subject->getChanges(),
                'original' => $subject->getRawOriginal(),
            ]));
            if (app()->environment('local', 'testing')) {
                throw $e;
            }
            report($e);
        }
    }
}
