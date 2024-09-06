<?php

declare(strict_types=1);

namespace App\Core;

use Sentry\State\Scope;
use Actions\Models\Action;

use function Sentry\configureScope;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use Actions\Core\ActionTranslator as BaseActionTranslator;

class ActionTranslator extends BaseActionTranslator
{
    public function buildChangesFromPayload(Action $action): array
    {
        return static::mapPayload($action->payload ?: [], function ($change, $original, $event, $field) use ($action) {
            if ($field === 'order' && $event === 'add') {
                return null;
            }
            if (\is_array($change) || \is_array($original)) {
                $type = 'array';
            } elseif (is_string_castable($change) || is_string_castable($original)) {
                $changeString = (string) $change;
                $originalString = (string) $original;
                $longest = max($changeString, $originalString);
                $type = mb_strlen($longest) > 255 ? 'paragraph' : 'line';
            } else {
                $type = null;
            }

            [$before, $after] = $this->formatBeforeAndAfter($action, $field, $original, $change);

            return [
                'description' => $this->translateEvent($action, $event, $field, $original, $change),
                'before' => $before,
                'after' => $after,
                'type' => $type,
            ];
        }, $this->getSilentFields($action));
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    public function formatBeforeAndAfter(Action $action, string $field, mixed $original, mixed $change): array
    {
        $before = $action->formatPayload($field, $original);
        $after = $action->formatPayload($field, $change);

        if ((! $this->isValidChange($before)) || (! $this->isValidChange($after))) {
            configureScope(function (Scope $scope) use ($action, $field, $before, $after) {
                $scope->setExtras([
                    'action' => $action->id,
                    'type' => $action->type->value,
                    'subject' => $action->subjectClass(),
                    'field' => $field,
                    'before' => $before,
                    'after' => $after,
                ]);
            });
            report(new \Exception("Action: [$action->id] change payload is not a string."));
            $before = null;
            $after = null;
        }

        return [$before, $after];
    }

    protected function isValidChange(mixed $change): bool
    {
        return $change === null
            || \is_string($change)
            || $change instanceof SyncPromise
            || $change instanceof Promise;
    }
}
