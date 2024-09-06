<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Actions\Core\ActionType;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Core\Mappings\FieldActionFormatter;
use Illuminate\Contracts\Config\Repository;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Contracts\Translation\Translator;

class ItemActionTranslator extends ActionTranslator
{
    protected GlobalId $globalId;

    public function __construct(Repository $config, Translator $translator, GlobalId $globalId)
    {
        parent::__construct($config, $translator);
        $this->globalId = $globalId;
    }

    public function actionChanges(Action $action): ?array
    {
        // If the action was to change relationships or delete an item then
        // there are no sub changes to report.
        if (
            $action->type->is(ActionType::DELETE())
            || $action->type->is(RelationshipActionType::RELATIONSHIP_ADDED())
            || $action->type->is(RelationshipActionType::RELATIONSHIP_REMOVED())
        ) {
            return null;
        }

        $payload = $action->payload ?? [];
        // If the action is CREATE then there are no `changes` or `original` fields,
        // so we set changes to the payload and original to an empty array.
        $dataChanges = $payload['changes']['data'] ?? $payload['data'] ?? [];
        $dataOriginal = $payload['original']['data'] ?? [];

        $dataChangeMessages = $this->getDataPayloadMessages($dataChanges, $dataOriginal);

        $otherChangeMessages = static::mapPayload($payload, function ($change, $original, $event, $field) use ($action) {
            if ($field === 'data') {
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

            if ($field === 'favorited_at') {
                $description = $this->translateEvent($action, $event, $field, $original ? 1 : 0, $change ? 1 : 0);
                $original = null;
                $change = null;
            } else {
                $description = $this->translateEvent($action, $event, $field, $original, $change);
            }

            return [
                'description' => $description,
                'before' => $original,
                'after' => $change,
                'type' => $type,
            ];
        });

        return array_filter([...$dataChangeMessages, ...$otherChangeMessages]);
    }

    public function getFormattedType(string $type, int $optionMask): string
    {
        if (
            $optionMask & FieldActionFormatter::IS_LIST
            || \in_array($type, ['PARAGRAPH', 'ADDRESS'], true)
        ) {
            return 'paragraph';
        }

        return 'line';
    }

    public function getDataPayloadMessages(mixed $changes, mixed $original, string $prefix = ''): array
    {
        $changeValues = Arr::map($changes, fn ($d) => $d[FieldActionFormatter::VAL]);

        $payload = [
            'changes' => $changeValues,
            'original' => $original,
        ];

        $payloadMessages = static::mapPayload(
            $payload,
            function ($changedVal, $originalVal, $event, $fieldId) use ($changes, $prefix) {
                $type = $changes[$fieldId][FieldActionFormatter::TYPE];
                $optionMask = $changes[$fieldId][FieldActionFormatter::OPTION_MASK];
                $field = $prefix.Str::wrap($changes[$fieldId][FieldActionFormatter::NAME] ?? $fieldId, '"');

                if ($type === FieldType::MULTI()->value) {
                    return $this->handleMulti($optionMask, $changedVal, $originalVal, $fieldId, $field);
                }

                $changeString = $this->formatFieldValue($type, $changedVal, $optionMask);
                $originalString = $originalVal ? $this->formatFieldValue($type, $originalVal, $optionMask) : null;
                $type = $this->getFormattedType($type, $optionMask);

                return [
                    'description' => trans("actions::description.field.$event", ['field' => $field]),
                    'before' => $originalString,
                    'after' => $changeString,
                    'type' => $type,
                ];
            }
        );

        return $this->flattenListArrays($payloadMessages);
    }

    /**
     * @return array|mixed[]
     */
    public function handleMulti(mixed $optionMask, array $changedVal, ?array $originalVal, string $fieldId, string $field): array
    {
        if ($optionMask & FieldActionFormatter::IS_LIST) {
            // If we are handed a list multi field then we loop through each
            // item and add an ordinal to the prefix (e.g. 1st, 2nd, 3rd, etc.)
            // remove the list flag from the option mask and call this method
            // again.
            return collect($changedVal)
                ->flatMap(function ($val, $key) use ($originalVal, $fieldId, $field, $optionMask) {
                    $place = $key + 1;
                    $ordinal = trans_choice('common.ordinal', (int) mb_substr((string) $place, -1), ['number' => $place]);

                    return $this->handleMulti(
                        $optionMask & ~FieldActionFormatter::IS_LIST,
                        $val,
                        $originalVal[$key] ?? [],
                        $fieldId,
                        "$ordinal $field",
                    );
                })->toArray();
        }

        $payload = ['changes' => $changedVal, 'original' => $originalVal ?? []];

        return static::mapPayload(
            $payload,
            function ($changedVal, $originalVal, $event, $subField) use ($field) {
                if ($subField === FieldActionFormatter::KEY) {
                    return null;
                }
                if ($subField === FieldActionFormatter::LABEL) {
                    return [
                        'description' => trans("actions::description.field.$event", ['field' => $field.' '.trans('common.label')]),
                        'before' => $originalVal ? "[$originalVal]" : null,
                        'after' => $changedVal ? "[$changedVal]" : null,
                        'type' => 'line',
                    ];
                }

                return $this->getDataPayloadMessages($changedVal, $originalVal, "$field → ");
            },
        );
    }

    protected function formatFieldValue(string $type, mixed $value, int $optionMask): string
    {
        return (new FieldActionFormatter)->resolveValue($value, $type, $optionMask);
    }

    protected function flattenListArrays(array $changes): array
    {
        $flattened = [];
        foreach ($changes as $key => $change) {
            if (\is_array($change) && array_is_list($change)) {
                $flattened = [...$flattened, ...$this->flattenListArrays($change)];
            } else {
                $flattened[$key] = $change;
            }
        }

        return $flattened;
    }
}
