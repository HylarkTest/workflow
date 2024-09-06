<?php

declare(strict_types=1);

namespace Actions\Core;

use Actions\Models\Action;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Config\Repository;
use Actions\Models\Contracts\ActionPerformer;
use Illuminate\Contracts\Translation\Translator;
use Actions\Models\Contracts\ModelActionTranslator;
use Actions\Models\Contracts\ActionSubjectNameProvider;
use Actions\Core\Contracts\ActionTranslator as ActionTranslatorInterface;

class ActionTranslator implements ActionTranslatorInterface
{
    public Repository $config;

    protected Translator $translator;

    public function __construct(Repository $config, Translator $translator)
    {
        $this->config = $config;
        $this->translator = $translator;
    }

    public function subjectName(Model $subject): string
    {
        return $this->getSubjectNameOrClassName($subject);
    }

    public function subjectTypeName(Model $subjectClass): string
    {
        $className = class_basename($subjectClass);
        $key = "actions::description.subject.$className";
        $typeName = $this->translator->get($key);

        return $typeName === $key ? $className : $typeName;
    }

    public function subjectNameFromAction(Action $action): string
    {
        return $this->getRelationNameFromAction(
            $this->getNamePersistenceConfig(true),
            $action,
            'subject_name',
            [$this, 'getSubjectNameOrClassNameFromAction']
        );
    }

    public function performerName(Model $performer): ?string
    {
        return $this->getPerformerNameIfExists($performer);
    }

    public function performerNameFromAction(Action $action): ?string
    {
        return $this->getRelationNameFromAction(
            $this->getNamePersistenceConfig(false),
            $action,
            'performer_name',
            [$this, 'getPerformerNameFromActionIfExists']
        );
    }

    public function setSubjectName(Action $action, Model $subject): Action
    {
        return $this->setRelationName($action, $subject);
    }

    public function setPerformerName(Action $action, Model $performer): Action
    {
        return $this->setRelationName($action, $performer, false);
    }

    public function actionDescription(Action $action, bool $withPerformer = true): string
    {
        $subjectClass = $action->subjectClass();

        if ($subjectClass instanceof ModelActionTranslator) {
            return $subjectClass::getActionDescription($action, $withPerformer);
        }

        return $this->translateDescription($action, $withPerformer);
    }

    public function actionChanges(Action $action): ?array
    {
        $subjectClass = $action->subjectClass();

        if ($subjectClass instanceof ModelActionTranslator) {
            return $subjectClass::getActionChanges($action);
        }

        if ($action->payload) {
            return $this->buildChangesFromPayload($action);
        }

        return null;
    }

    protected function getSilentFields(Action $action): array
    {
        $subjectClass = $action->subjectClass();
        $subject = new $subjectClass;
        $silentFields = $this->config->get('actions.silent');
        if (method_exists($subject, 'getActionSilentFields')) {
            $silentFields = array_merge($silentFields, $subject->getActionSilentFields());
        }
        if (method_exists($subject, 'getActionIgnoredColumns')) {
            $silentFields = array_merge($silentFields, $subject->getActionIgnoredColumns());
        }

        return $silentFields;
    }

    public function buildChangesFromPayload(Action $action): array
    {
        return static::mapPayload($action->payload ?: [], function ($change, $original, $event, $field) use ($action) {
            $description = $this->translateEvent($action, $event, $field, $original, $change);

            return $description ? [
                'description' => $description,
                'before' => $action->formatPayload($field, $original),
                'after' => $action->formatPayload($field, $change),
            ] : null;
        }, $this->getSilentFields($action));
    }

    /**
     * Map over a payload array and convert it to an array of changes defined
     * by an optional callback.
     */
    public static function mapPayload(array $payload, ?\Closure $buildChangeCb, array $silentFields = []): array
    {
        /*
         * By default the callback will just grab the before and after states of
         * the action. But you could use the event type and field name to build
         * more customized change arrays.
         */
        $buildChangeCb = $buildChangeCb ?: static function ($change, $original) {
            return [
                'before' => $original,
                'after' => $change,
            ];
        };

        /*
         * The payload of CREATE actions typically just have the attributes of
         * the created subject as the payload in which case the "original"
         * changes would just be an empty array as there was no prior state.
         * UPDATE actions will have the 'changes' and 'original' arrays in the
         * payload.
         */
        if (isset($payload['changes'])) {
            $changes = $payload['changes'];
            $original = $payload['original'] ?? [];
        } else {
            $changes = $payload;
            $original = [];
        }

        return collect([...array_keys($changes), ...array_keys($original)])
            ->unique()
            ->filter(static function ($key) use ($silentFields) {
                return ! in_array($key, $silentFields, true);
            })
            // Here we map through every key in the payload and work out if that
            // key was added, removed or changed based on if that key exists in
            // the original and changes arrays.
            // Then we can pass the event to the callback so that can be used to
            // create a human-readable message.
            ->map(static function ($key) use ($changes, $original, $buildChangeCb) {
                if (isset($changes[$key]) && ! isset($original[$key])) {
                    $event = 'add';
                } elseif (isset($original[$key]) && ! isset($changes[$key])) {
                    $event = 'remove';
                } else {
                    $event = 'change';
                }

                return $buildChangeCb($changes[$key] ?? null, $original[$key] ?? null, $event, $key);
            })
            ->filter()
            ->values()
            ->all();
    }

    public function translateEvent(Action $action, string $event, string $field, mixed $original = null, mixed $change = null): string
    {
        $classKey = Str::snake(class_basename($action->subjectClass()));
        $type = $action->type;

        $possibleKeys = [
            "actions::description.$classKey.field.$type->key.$field.$event", // actions::description.post.field.CREATE.name.add
            "actions::description.$classKey.field.$field.$event", // actions::description.post.field.name.add
            "actions::description.$classKey.field.$type->key.$event", // actions::description.post.field.CREATE.add
            "actions::description.$classKey.field.$event", // actions::description.post.field.add
            "actions::description.$classKey.field.$field", // actions::description.post.field
            "actions::description.field.$type->key.$field.$event", // actions::description.field.CREATE.name.add
            "actions::description.field.$type->key.$event", // actions::description.field.CREATE.add
            "actions::description.field.$field.$event", // actions::description.field.name.add
            "actions::description.field.$event", // actions::description.field.add
        ];
        $field = $this->translateField($field, $classKey);

        foreach ($possibleKeys as $key) {
            $translation = $this->translator->get($key, ['field' => $field]);
            if (\is_string($translation) && $translation !== $key) {
                if (Str::contains($translation, '|')) {
                    $translation = $this->translator->choice($key, (int) $change, ['field' => $field]);
                }

                return $translation;
            }
        }

        return $translation;
    }

    public function translateDescription(Action $action, bool $withPerformer): string
    {
        $subjectClass = $action->subjectClass();

        $name = $this->subjectNameFromAction($action);

        $translationKey = "actions::description.$action->type";
        $keyWithModel = 'actions::description.'.Str::snake(class_basename($subjectClass)).".$action->type";

        $payloadKeys = array_filter(Arr::dot(['payload' => $action->payload]));
        $payloadKeys['subject'] = $name;
        $payloadKeys['subjectType'] = $this->subjectTypeName($subjectClass);

        if (
            $withPerformer
            && $performerName = $this->performerNameFromAction($action)
        ) {
            $key = $this->translator->has($keyWithModel) ? $keyWithModel : $translationKey;
            $payloadKeys['performer'] = $performerName;

            return $this->translator->choice(
                $key,
                2,
                $payloadKeys,
            );
        }
        $key = $this->translator->has($keyWithModel) ? $keyWithModel : $translationKey;

        return $this->translator->choice($key, 1, $payloadKeys);
    }

    protected function translateField(string $field, string $classKey): string
    {
        $field = Str::snake($field);

        $possibleKeys = [
            "actions::description.attributes.$field",
            "actions::description.$classKey.attributes.$field",
        ];

        foreach ($possibleKeys as $key) {
            $translation = $this->translator->get($key);
            if (\is_string($translation) && $translation !== $key) {
                return $translation;
            }
        }

        return $field;
    }

    /**
     * Get the subject name of the model.
     */
    protected function getSubjectNameOrClassName(Model $subject): string
    {
        if ($subject instanceof ActionSubjectNameProvider) {
            return $subject->getActionSubjectName() ?: class_basename($subject);
        }

        return class_basename($subject);
    }

    /**
     * Get the name of the subject of an action.
     */
    protected function getSubjectNameOrClassNameFromAction(Action $action): ?string
    {
        return $action->subject ? $this->getSubjectNameOrClassName($action->subject) : null;
    }

    /**
     * Get the performer name of the model.
     */
    protected function getPerformerNameIfExists(Model $performer): ?string
    {
        if ($performer instanceof ActionPerformer) {
            return $performer->getActionPerformerName();
        }

        return null;
    }

    /**
     * Get the name of the performer that generated the action if it can be
     * found.
     */
    protected function getPerformerNameFromActionIfExists(Action $action): ?string
    {
        $performer = $action->performer;

        return $performer ? $this->getPerformerNameIfExists($performer) : null;
    }

    /**
     * Get the subject/performer name from the action if it is saved on the
     * action. If not, use the fallback argument to get the name from the
     * subject/performer model.
     *
     * @phpstan-param callable(\Actions\Models\Action): ?string $fallback
     */
    protected function getRelationNameFromAction(
        NamePersistenceConfig $config,
        Action $action,
        string $column,
        callable $fallback
    ): string {
        return match (true) {
            $config->isNever() => $fallback($action) ?: '',
            $config->isAlways(), $config->isOnUpdate() => $action->$column,
            $config->isDeleteOrSoftDelete() => $action->$column ?: ($fallback($action) ?: ''),
            default => '',
        };
    }

    /**
     * Retrieve the configuration that defines if/how the performer/subject name
     * should be stored on the action.
     */
    protected function getNamePersistenceConfig(bool $forSubject = true): NamePersistenceConfig
    {
        $type = $forSubject ? 'subject' : 'performer';

        $key = "actions.save_{$type}_name";

        $config = $this->config->get($key);

        return $config instanceof NamePersistenceConfig ?
            $config :
            NamePersistenceConfig::from($config);
    }

    /**
     * Set the subject/performer name on the action.
     */
    protected function setRelationName(Action $action, Model $model, bool $isSubject = true): Action
    {
        $type = $isSubject ? 'subject' : 'performer';

        $saveNameConfig = $this->getNamePersistenceConfig($isSubject);
        if ($saveNameConfig->isAlways() || $saveNameConfig->isOnUpdate()) {
            $action->{$type.'_name'} = $this->{"{$type}Name"}($model);
        }

        return $action;
    }
}
