<?php

declare(strict_types=1);

namespace Actions\Core;

use Actions\Models\Action;
use Illuminate\Support\Arr;
use Actions\Jobs\RecordAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Bus\Dispatcher;
use Actions\Models\Contracts\ActionLimiter;
use Illuminate\Contracts\Config\Repository;
use Actions\Models\Contracts\SoftDeleteModel;
use Actions\Models\Contracts\ModelActionRecorder;
use Actions\Models\Contracts\ActionPerformerProvider;
use Actions\Core\Contracts\ActionRecorder as ActionRecorderInterface;

class ActionRecorder implements ActionRecorderInterface
{
    /**
     * @var \Closure(\Illuminate\Database\Eloquent\Model): ?\Illuminate\Database\Eloquent\Model|null$userResolver
     */
    protected ?\Closure $userResolver;

    protected ?ActionType $currentActionType = null;

    protected static bool $skipRecording = false;

    protected static ?Model $performer = null;

    /**
     * ActionRecorder constructor.
     */
    public function __construct(
        protected Repository $config,
        protected Dispatcher $queue
    ) {}

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function record(Model $model, ?Model $performer = null, bool $force = false): Action|array|null
    {
        if (! $performer) {
            $performer = $model instanceof ActionPerformerProvider ?
                $model->getPerformer() :
                $this->resolveUser($model);
        }

        if (! $this->shouldRecord($model, $performer, $force)) {
            return null;
        }

        if ($model instanceof ModelActionRecorder) {
            $type = $model->getActionType($performer, $this->currentActionType);
            $payload = $model->getActionPayload($type, $performer);
        } else {
            $type = $this->getType($model);
            $payload = $this->getPayload($model, $type, $performer);
        }

        return $this->dispatchOrCreateAction($model, $performer, $type, $payload);
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function recordWithPayload(Model $model, ActionType $type, array $payload, ?Model $performer = null, bool $force = false): Action|array|null
    {
        if (! $performer) {
            $performer = $model instanceof ActionPerformerProvider ?
                $model->getPerformer() :
                $this->resolveUser($model);
        }

        if (! $this->shouldRecord($model, $performer, $force)) {
            return null;
        }

        return $this->dispatchOrCreateAction($model, $performer, $type, $payload);
    }

    public function dispatchOrCreateAction(Model $model, ?Model $performer, ActionType $type, ?array $payload): Action|array|null
    {
        if ($this->config->get('actions.queue')) {
            $this->queue->dispatch(
                new RecordAction(
                    $model, $performer, $type, $payload, now()
                )
            );
        }

        return Action::createAction($model, $performer, $type, $payload);
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     *
     * @throws \Exception
     */
    public function recordEvent(string $event, Model $model, ?Model $performer = null, bool $force = false): Action|array|null
    {
        $type = ActionType::fromEvent($event);

        return $this->recordType($type, $model, $performer, $force);
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     *
     * @throws \Exception
     */
    public function recordType(ActionType $type, Model $model, ?Model $performer = null, bool $force = false): Action|array|null
    {
        $this->currentActionType = $type;

        $action = $this->record($model, $performer, $force);

        $this->currentActionType = null;

        return $action;
    }

    public function getType(Model $model): ActionType
    {
        if ($this->currentActionType !== null) {
            return $this->currentActionType;
        }
        if ($model->wasRecentlyCreated) {
            return ActionType::CREATE();
        }
        if ($model instanceof SoftDeleteModel) {
            $deletedAtColumn = $model->getDeletedAtColumn();
            if ($model->wasChanged($deletedAtColumn) && $model->getAttribute($deletedAtColumn) === null) {
                return ActionType::RESTORE();
            }
            /*
             * If the model was soft deleted there is no record to show that was
             * the action that just happened, so we have to guess. However there
             * shouldn't be any reason to do anything to a soft deleted model so
             * it is reasonable to assume that if the model has been soft
             * deleted then that is the action we want to record.
             */
            if ($model->getAttribute($deletedAtColumn) !== null) {
                return ActionType::DELETE();
            }
        }
        if ($model->exists) {
            return ActionType::UPDATE();
        }

        return ActionType::DELETE();
    }

    public function shouldRecord(Model $model, ?Model $performer = null, bool $force = false): bool
    {
        if (static::$skipRecording) {
            return false;
        }
        /*
         * If the model doesn't have a key it has not been saved to the database
         * so we cannot record the action as there will be no subject.
         */
        if ($model->getKey() === null) {
            return false;
        }

        if (! $force && $performer === null && $this->config->get('actions.mandatory_performer')) {
            return false;
        }

        /*
         * The model can decide if it should record or not.
         */
        if ($model instanceof ActionLimiter) {
            return $model->shouldRecordAction($performer, $force);
        }

        return true;
    }

    public function getPayload(Model $model, ActionType $type, ?Model $performer = null): ?array
    {
        if ($type->is(ActionType::CREATE())) {
            return $this->parsePayload($model->getAttributes(), $model);
        }
        if ($type->is(ActionType::UPDATE())) {
            $changes = $this->parsePayload($model->getChanges(), $model);
            $original = $this->parsePayload(Arr::only($model->getRawOriginal(), array_keys($model->getChanges())), $model);

            return [
                'changes' => $changes,
                'original' => $original,
            ];
        }

        return null;
    }

    /**
     * @param  \Closure(\Illuminate\Database\Eloquent\Model): ?\Illuminate\Database\Eloquent\Model  $resolver
     * @return $this
     */
    public function setUserResolver(\Closure $resolver): static
    {
        $this->userResolver = $resolver;

        return $this;
    }

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Model): ?\Illuminate\Database\Eloquent\Model
     */
    public function getUserResolver(): \Closure
    {
        return $this->userResolver ?? fn (Model $model) => null;
    }

    public function resolveUser(Model $subject): ?Model
    {
        if (isset(static::$performer)) {
            return static::$performer;
        }
        $resolver = $this->getUserResolver();

        return $resolver($subject);
    }

    public function parsePayload(array $payload, Model $subject): array
    {
        $ignoredColumns = $this->config->get('actions.ignore');
        if (method_exists($subject, 'getActionIgnoredColumns')) {
            $ignoredColumns = array_merge($ignoredColumns, $subject->getActionIgnoredColumns());
        }
        if ($ignoredColumns) {
            Arr::forget($payload, $ignoredColumns);
        }

        return array_filter($payload, 'filled') ?: [];
    }

    public static function withoutRecording(\Closure $cb): void
    {
        $oldSkipRecording = static::$skipRecording;
        static::$skipRecording = true;

        try {
            $cb();
        } finally {
            static::$skipRecording = $oldSkipRecording;
        }
    }

    /**
     * @template T
     *
     * @param  \Closure(): T  $cb
     * @return T
     */
    public static function withPerformer(Model $performer, \Closure $cb): mixed
    {
        $oldPerformer = static::$performer;
        static::$performer = $performer;

        try {
            return $cb();
        } finally {
            static::$performer = $oldPerformer;
        }
    }
}
