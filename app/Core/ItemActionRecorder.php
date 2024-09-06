<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Item;
use Illuminate\Support\Arr;
use Actions\Core\ActionType;
use Actions\Core\ActionRecorder;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Fields\Field;
use App\Core\Mappings\FieldActionFormatter;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\FieldCollection;

class ItemActionRecorder extends ActionRecorder
{
    /**
     * @param  \App\Models\Item  $model
     *
     * @throws \JsonException
     */
    public function getPayload(Model $model, ActionType $type, ?Model $performer = null): ?array
    {
        if ($type->is(ActionType::CREATE())) {
            return $this->buildCreatePayloadFromAttributes($model);
        }
        if ($type->is(ActionType::UPDATE())) {
            return $this->buildUpdatePayloadFromAttributes($model);
        }

        return null;
    }

    public function buildCreatePayloadFromAttributes(Item $model): array
    {
        $payload = $this->parsePayload($model->getAttributes(), $model);
        $payload['data'] = $this->formatActionDataPayload($model, json_decode($payload['data'], true, 512, \JSON_THROW_ON_ERROR));

        return $payload;
    }

    public function buildUpdatePayloadFromAttributes(Item $model): array
    {
        $changes = $model->getChanges();
        $original = $model->getRawOriginal();
        $original = $this->parsePayload(Arr::only($original, array_keys($changes)), $model);
        $changes = $this->parsePayload($changes, $model);

        if (isset($changes['data'])) {
            $fields = $model->mapping->fields;
            $dataChanges = json_decode($changes['data'], true);
            $dataOriginal = json_decode($original['data'] ?? '[]', true);
            $dataChanges = $this->filterRecursively(
                $dataChanges,
                $dataOriginal,
                fn ($change, $original) => blank($original) || $original !== $change,
                $fields
            );
            $changes['data'] = $this->formatActionDataPayload($model, $dataChanges);

            $dataOriginal = $this->filterRecursively(
                $dataOriginal,
                $dataChanges,
                fn ($original, $change) => filled($change),
                $fields,
            );
            $original['data'] = $this->formatActionDataPayload($model, $dataOriginal, false);
        }

        return [
            'changes' => $changes,
            'original' => $original,
        ];
    }

    protected function filterRecursively(array $changes, array $original, callable $callback, FieldCollection $fields): array
    {
        $result = [];
        foreach ($changes as $key => $change) {
            $originalValue = $original[$key] ?? null;
            /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
            $field = $fields->find($key);
            if ($field && $change && $field->type()->is(FieldType::MULTI())) {
                /** @var \Mappings\Core\Mappings\Fields\Types\MultiField $field */
                if (is_array($change) && array_key_exists(Field::LIST_VALUE, $change)) {
                    $change[Field::LIST_VALUE] = $this->filterRecursively($change[Field::LIST_VALUE], $originalValue[Field::LIST_VALUE] ?? [], $callback, $field->fields());
                } else {
                    $change = $this->filterRecursively($change, $originalValue ?? [], $callback, $field->fields());
                }
                if ($change) {
                    $result[$key] = $change;
                }
            } elseif ($callback($change, $originalValue)) {
                $result[$key] = $change;
            }
        }

        return $result;
    }

    protected function formatActionDataPayload(Item $model, array $payload, bool $withFieldInfo = true): array
    {
        $fields = $model->mapping->fields;
        $formattedPayload = [];
        foreach ($payload as $fieldId => $value) {
            /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
            $field = $fields->find($fieldId);
            if (! $field) {
                continue;
            }
            $formattedPayload[$fieldId] = (new FieldActionFormatter)->formatFieldValueForAction($value, $field, $withFieldInfo);
        }

        return $formattedPayload;
    }
}
