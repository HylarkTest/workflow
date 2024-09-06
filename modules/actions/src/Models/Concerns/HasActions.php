<?php

declare(strict_types=1);

namespace Actions\Models\Concerns;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class HasActions
 *
 * @mixin Model
 *
 * Relationships
 *
 * @property \Illuminate\Database\Eloquent\Collection<\Actions\Models\Action> $actions
 * @property \Actions\Models\Action $createAction
 * @property \Actions\Models\Action $latestAction
 * @property \Illuminate\Database\Eloquent\Collection<\Actions\Models\Action> $updateActions
 * @property \Illuminate\Database\Eloquent\Collection<\Actions\Models\Action> $restoreActions
 * @property \Illuminate\Database\Eloquent\Collection<\Actions\Models\Action> $deleteActions
 */
trait HasActions
{
    use HasActionEvents;
    use RecordsActions;

    protected static bool $disableActionRecording = false;

    /**
     * Register listeners on the actionable model.
     */
    public static function bootHasActions(): void
    {
        if (Action::config('automatic')) {
            static::getActionEventManager()->listenToModelEvents(static::class);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function actions(): MorphMany
    {
        return $this->morphMany($this->actionClass(), 'subject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Actions\Models\Action>
     */
    public function singleAction(): MorphOne
    {
        return $this->morphOne($this->actionClass(), 'subject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function actionsOfType(ActionType $type): MorphMany
    {
        return $this->actions()->where('type', $type);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Actions\Models\Action>
     */
    public function actionOfType(ActionType $type): MorphOne
    {
        return $this->singleAction()->where('type', $type);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Actions\Models\Action>
     */
    public function createAction(): MorphOne
    {
        return $this->actionOfType(ActionType::CREATE());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function deleteActions(): MorphMany
    {
        return $this->actionsOfType(ActionType::DELETE());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function restoreActions(): MorphMany
    {
        return $this->actionsOfType(ActionType::RESTORE());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Actions\Models\Action>
     */
    public function latestAction(): MorphOne
    {
        return $this->singleAction()->where('is_latest', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Actions\Models\Action>
     */
    public function updateActions(): MorphMany
    {
        return $this->actionsOfType(ActionType::UPDATE());
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    public function recordAction(?Model $performer = null, bool $force = false): Action|array|null
    {
        return static::getActionRecorder()->record($this, $performer, $force);
    }

    public function getSubjectDisplayNameKey(): string
    {
        /** @phpstan-ignore-next-line Not sure how to do this any other way */
        return property_exists($this, 'subjectDisplayNameKey') ? $this->subjectDisplayNameKey : 'name';
    }

    public function subjectNameWasChanged(): bool
    {
        return $this->wasChanged($this->getSubjectDisplayNameKey());
    }

    public function getActionSubjectName(): ?string
    {
        return $this->{$this->getSubjectDisplayNameKey()} ?? null;
    }

    public function getActionIgnoredColumns(): array
    {
        /** @phpstan-ignore-next-line Not sure how to do this any other way */
        return property_exists($this, 'actionIgnoredColumns')
            ? $this->actionIgnoredColumns
            : [];
    }

    public function getActionSilentFields(): array
    {
        /** @phpstan-ignore-next-line Not sure how to do this any other way */
        return property_exists($this, 'actionSilentFields')
            ? $this->actionSilentFields
            : [];
    }

    public function shouldRecordAction(?Model $performer, bool $force): bool
    {
        return $force || ! static::$disableActionRecording;
    }

    public static function withoutActions(\Closure $callback): mixed
    {
        static::$disableActionRecording = true;

        try {
            return $callback();
        } finally {
            static::$disableActionRecording = false;
        }
    }

    public static function customAction(Action $baseAction): Action
    {
        if ($className = static::customActionClass($baseAction->type)) {
            return (new $className)->newFromBuilder($baseAction->getAttributes());
        }

        return $baseAction;
    }

    /**
     * @return class-string<\Actions\Models\Action>|null
     */
    public static function customActionClass(ActionType $type): ?string
    {
        if ($customActionClass = ActionType::$customActions[$type->value] ?? false) {
            return $customActionClass;
        }
        /** @phpstan-ignore-next-line Not sure how to do this any other way */
        if (property_exists(static::class, 'customActions')) {
            /** @phpstan-ignore-next-line Not sure how to do this any other way */
            return static::$customActions[$type->key] ?? null;
        }

        return null;
    }

    public function getActionSubject(Action $action): Model
    {
        return $this;
    }

    public function actionClass(): string
    {
        return $this instanceof ActionClassProvider
            ? $this->getActionClass()
            : Action::baseClass();
    }
}
