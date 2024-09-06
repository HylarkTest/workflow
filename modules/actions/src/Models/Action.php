<?php

declare(strict_types=1);

namespace Actions\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Actions\Core\ActionType;
use Illuminate\Support\Carbon;
use Actions\Core\ActionDatabaseManager;
use Illuminate\Database\Eloquent\Model;
use LighthouseHelpers\Concerns\HasGlobalId;
use Actions\Core\Contracts\ActionTranslator;
use Illuminate\Database\Eloquent\Collection;
use Actions\Models\Concerns\ActionClassProvider;
use Actions\Models\Contracts\CustomActionProvider;
use Actions\Models\Contracts\ActionSubjectProvider;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Actions\Models\Contracts\ActionTranslatorProvider;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Scopes\LatestIfNotOrderedScope;

/**
 * Class Action
 *
 * @property \Actions\Core\ActionType $type
 * @property string|null $performer_name
 * @property string|null $subject_name
 * @property string $performer_type
 * @property string $subject_type
 * @property array|null $payload
 * @property bool $is_latest
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Model|null $performer
 * @property \Illuminate\Database\Eloquent\Model|null $subject
 * @property \Illuminate\Database\Eloquent\Collection<int, \Actions\Models\Action> $childActions
 * @property \Actions\Models\Action $parentAction
 */
class Action extends Model
{
    use ConvertsCamelCaseAttributes;
    use HasGlobalId;

    /**
     * @var string
     */
    protected $table = 'actions';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'json',
        'is_latest' => 'boolean',
        'type' => ActionType::class,
    ];

    /**
     * @var array<\Illuminate\Database\Eloquent\Model>
     */
    protected static array $subjectClasses = [];

    /**
     * @var array<\Illuminate\Database\Eloquent\Model>
     */
    protected static array $performerClasses = [];

    protected static ActionType $currentActionType;

    protected static ActionTranslator $translator;

    /**
     * @var \Actions\Core\ActionType[]
     */
    protected static array $subjectActionTypes = [];

    /**
     * @var \Actions\Models\Action[]|null
     */
    protected static ?array $batchActions = null;

    protected static ?self $parent = null;

    public ?Model $triggeringModel = null;

    public function setSubject(Model $subject): self
    {
        $this->subject()->associate($subject);

        $this->getSubjectActionTranslator()?->setSubjectName($this, $subject);

        return $this;
    }

    public function setPerformer(Model $performer): self
    {
        $this->performer()->associate($performer);

        $this->getSubjectActionTranslator()?->setPerformerName($this, $performer);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \Actions\Models\Action>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Actions\Models\Action>
     */
    public function childActions(): HasMany
    {
        return $this->hasMany(static::config('model'), 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Actions\Models\Action, \Actions\Models\Action>
     */
    public function parentAction(): BelongsTo
    {
        return $this->belongsTo(static::config('model'), 'parent_id');
    }

    public function subjectClass(): Model
    {
        if (! isset(static::$subjectClasses[$this->subject_type])) {
            static::$subjectClasses[$this->subject_type] = $this->subject()->getRelated();
        }

        return static::$subjectClasses[$this->subject_type];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \Actions\Models\Action>
     */
    public function performer(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function performerClass(): Model
    {
        if (! isset(static::$performerClasses[$this->performer_type])) {
            static::$performerClasses[$this->performer_type] = $this->performer()->getRelated();
        }

        return static::$performerClasses[$this->performer_type];
    }

    public function description(bool $withPerformer = true): string
    {
        return $this->getSubjectActionTranslator()?->actionDescription($this, $withPerformer) ?: '';
    }

    public function changes(): ?array
    {
        return $this->getSubjectActionTranslator()?->actionChanges($this);
    }

    public function __toString()
    {
        return $this->description();
    }

    public static function config(string $key): mixed
    {
        return config('actions.'.$key);
    }

    public function getSubjectActionTranslator(): ?ActionTranslator
    {
        $subjectClass = $this->subjectClass();
        if ($subjectClass instanceof ActionTranslatorProvider) {
            return $subjectClass::getActionTranslator();
        }

        return static::getActionTranslator();
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Actions\Models\Action>
     */
    public function hydrate(array $items): Collection
    {
        return $this->newCollection(array_map(function ($item) {
            $action = $this->newFromBuilder($item);
            $subjectClass = $action->subjectClass();
            if ($subjectClass instanceof CustomActionProvider) {
                return $subjectClass::customAction($action);
            }

            return $action;
        }, $items));
    }

    public function formatPayload(string $field, mixed $payload): mixed
    {
        $subject = $this->subjectClass();
        $method = 'format'.Str::studly($field).'ActionPayload';
        if (method_exists($subject, $method)) {
            return $subject::$method($payload, $this);
        }
        if (method_exists($subject, 'formatActionPayload')) {
            return $subject::formatActionPayload($field, $payload);
        }

        return $payload;
    }

    public static function boot(): void
    {
        parent::boot();

        static::created(static function (self $action) {
            ActionDatabaseManager::updateIsLatest($action);
        });

        static::addGlobalScope(new LatestIfNotOrderedScope);
    }

    /**
     * @return class-string<\Actions\Models\Action>
     */
    public static function baseClass(): string
    {
        return static::config('model');
    }

    public static function getActionTranslator(): ActionTranslator
    {
        return static::$translator;
    }

    public static function setActionTranslator(ActionTranslator $translator): void
    {
        static::$translator = $translator;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function createAction(Model $subject, ?Model $performer, ActionType $type, ?array $payload = null, ?Carbon $time = null): self
    {
        $actionClass = $subject instanceof ActionClassProvider
            ? $subject->getActionClass()
            : static::config('model');

        /** @var \Actions\Models\Action $action */
        $action = new ($actionClass);

        if ($performer) {
            $action->setPerformer($performer);
        } elseif (static::config('mandatory_performer')) {
            throw new \InvalidArgumentException('Cannot create an action with no performer when `mandatory_performer` is set');
        }

        $action->type = $type;

        $action->payload = $payload;

        $action->triggeringModel = $subject;
        $action->setSubject($subject instanceof ActionSubjectProvider ? $subject->getActionSubject($action) : $subject);

        if (static::$batchActions !== null && ! $time) {
            $time = now();
        }

        if ($time) {
            $action->created_at = $time;
            $action->updated_at = $time;
            $action->timestamps = false;
        }

        if (static::$parent) {
            $action->parentAction()->associate(static::$parent);
        }

        if (static::$batchActions !== null) {
            static::$batchActions[] = $action;
        } else {
            $action->save();
        }

        return $action;
    }

    public static function batchRecord(\Closure $cb): void
    {
        if (static::$batchActions === null) {
            static::$batchActions = [];
        }
        try {
            $cb();
            if (! empty(static::$batchActions)) {
                $query = static::$batchActions[0]->newModelQuery();
                $query->insert(
                    array_map(fn (Action $action) => $action->getAttributesForInsert(), static::$batchActions)
                );
                ActionDatabaseManager::syncIsLatest();
            }
        } finally {
            static::$batchActions = null;
        }
    }

    /**
     * @param  \Closure(): mixed  $cb
     */
    public static function withParent(?self $action, \Closure $cb): void
    {
        $oldParent = static::$parent;

        static::$parent = $action;

        try {
            $cb();
        } finally {
            static::$parent = $oldParent;
        }
    }

    public function typeName(): string
    {
        return 'Action';
    }

    public function payloadHasField(?array $payload, string $field): bool
    {
        if (! $payload) {
            return false;
        }
        if (isset($payload['changes']) || isset($payload['original'])) {
            return static::getNestedFields($payload['changes'] ?? [], $field)
                || static::getNestedFields($payload['original'] ?? [], $field);
        }

        return static::getNestedFields($payload, $field);
    }

    protected static function getNestedFields(?array $payload, string $field): mixed
    {
        [$field, $nested] = explode('.', $field, 2);
        $value = $payload[$field] ?? null;
        if ($nested && $value) {
            return Arr::get(json_decode($value, true, 512, JSON_THROW_ON_ERROR), $nested);
        }

        return $value;
    }

    public static function getPayloadChanges(?array $payload, string $field): mixed
    {
        if (! $payload) {
            return null;
        }
        if (isset($payload['changes']) || isset($payload['original'])) {
            return static::getNestedFields($payload['changes'] ?? [], $field);
        }

        return static::getNestedFields($payload, $field);
    }

    public static function getPayloadOriginal(?array $payload, string $field): mixed
    {
        if (! $payload) {
            return null;
        }
        if (isset($payload['changes']) || isset($payload['original'])) {
            return static::getNestedFields($payload['original'] ?? [], $field);
        }

        return null;
    }

    public static function getPayloadDifferences(?array $payload, string $field): array
    {
        $changes = static::getPayloadChanges($payload, $field);
        $original = static::getPayloadOriginal($payload, $field);

        $differences = [
            'added' => [],
            'removed' => [],
            'changed' => [],
        ];

        if ($changes === $original) {
            return $differences;
        }
        if ($changes && ! $original) {
            $differences['added'] = $changes;
        } elseif ($original && ! $changes) {
            $differences['removed'] = $original;
        } elseif (is_array($changes) || is_array($original)) {
            collect([...array_keys($changes), ...array_keys($original ?? [])])
                ->unique()
                ->each(static function ($key) use ($changes, $original, &$differences) {
                    if (isset($changes[$key]) && ! isset($original[$key])) {
                        $differences['added'][$key] = $changes[$key];
                    } elseif (isset($original[$key]) && ! isset($changes[$key])) {
                        $differences['removed'][$key] = $original[$key];
                    } elseif ($changes[$key] !== $original[$key]) {
                        $differences['changed'][$key] = [
                            'before' => $original[$key],
                            'after' => $changes[$key],
                        ];
                    }
                });
        } else {
            $differences['changed'] = [
                'before' => $original,
                'after' => $changes,
            ];
        }

        return $differences;
    }

    public function updatePayloadChanges(callable $cb, bool $deleteIfEmpty = true): void
    {
        $payload = $this->payload;
        if (! $payload) {
            return;
        }
        if (isset($payload['changes']) || isset($payload['original'])) {
            $changes = $cb($payload['changes'] ?? [], $payload['original'] ?? [], $payload);
            $original = $cb($payload['original'] ?? [], $payload['changes'] ?? [], $payload);
            $payload = array_filter([
                'changes' => $changes,
                'original' => $original,
            ]);
        } else {
            $payload = $cb($payload, [], $payload);
        }

        if ($deleteIfEmpty && empty($payload) && $this->type->isNot(ActionType::CREATE())) {
            $this->delete();
        } else {
            $this->timestamps = false;
            $this->payload = $payload;
            $this->save();
        }
    }
}
