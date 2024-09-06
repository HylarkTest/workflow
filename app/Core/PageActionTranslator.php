<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use Illuminate\Support\Arr;

class PageActionTranslator extends ActionTranslator
{
    public function buildChangesFromPayload(Action $action): array
    {
        $payload = $action->payload ?: [];

        if (isset($payload['changes'])) {
            $payload = [
                'changes' => $this->updatePayload($payload['changes']),
                'original' => $this->updatePayload($payload['original']),
            ];
        } else {
            $payload = $this->updatePayload($payload);
        }

        return static::mapPayload($payload ?: [], function ($change, $original, $event, $field) use ($action) {
            if (str_starts_with($field, 'view')) {
                $field = 'view';
                if ($event === 'add' && \in_array($change['id'], ['TILE', 'LINE', 'SPREADSHEET'], true)) {
                    $event = 'change';
                }
                $change = $change['name'] ?? null;
                $original = $original['name'] ?? null;
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
            if ($original === $change) {
                return null;
            }

            [$before, $after] = $this->formatBeforeAndAfter($action, $field, $original, $change);

            return [
                'description' => $this->translateEvent($action, $event, $field),
                'before' => $before,
                'after' => $after,
                'type' => $type,
            ];
        }, $this->getSilentFields($action));
    }

    protected function updatePayload(array $payload): array
    {
        $config = $payload['config'] ?? [];
        $personalSettings = $payload['personalSettings'] ?? [];

        unset($payload['config']);
        unset($payload['personalSettings']);

        if ($config) {
            foreach (json_decode($config, true) as $key => $value) {
                if ($value) {
                    Arr::set($payload, $key, $value);
                }
            }
        }
        if ($personalSettings) {
            foreach (json_decode($personalSettings, true) as $key => $value) {
                if ($value) {
                    Arr::set($payload, $key, $value);
                }
            }
        }

        $design = $payload['design'] ?? [];

        unset($payload['design']);

        if ($design) {
            $design = json_decode($design, true);
            foreach ($design as $key => $value) {
                if ($key === 'views') {
                    foreach ($value as $index => $view) {
                        Arr::set($payload, "view$index", $view);
                    }
                } elseif ($key === 'defaultView') {
                    Arr::set($payload, 'defaultView', Arr::first($design['views'] ?? [], fn ($view) => $view['id'] === $value)['name'] ?? $value);
                } elseif ($value) {
                    Arr::set($payload, $key, $value);
                }
            }
        }

        return $payload;
    }
}
