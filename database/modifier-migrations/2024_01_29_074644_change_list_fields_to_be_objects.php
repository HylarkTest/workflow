<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Page;
use App\Models\Action;
use Actions\Core\ActionType;
use App\Core\Pages\PageType;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Fields\Field;
use LaravelUtils\Database\KnowsConnection;
use App\Core\Mappings\FieldActionFormatter;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Database\Migrations\Migration;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Mappings\Core\Mappings\Fields\Types\MultiField;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Model::withoutEvents(function () {
            Item::withTrashed()
                ->orderByDesc('id')
                ->each(function (Item $item) {
                    $data = json_decode($item->getRawOriginal('data'), true, 512, \JSON_THROW_ON_ERROR);
                    $newData = $this->recursivelyChangeValuesToObjects($data, $item->mapping->fields);
                    $item->timestamps = false;
                    $item->update(['data' => $newData]);
                });

            Action::query()
                ->where('subject_type', (new Item)->getMorphClass())
                ->whereIn('type', ['CREATE', 'UPDATE'])
                ->orderByDesc('id')
                ->each(function (Action $action) {
                    $payload = $action->getRawOriginal('payload');
                    if (! $payload) {
                        return;
                    }
                    $data = json_decode($payload, true, 512, \JSON_THROW_ON_ERROR);
                    if ($action->type->is(ActionType::CREATE())) {
                        if (! isset($data['data'])) {
                            return;
                        }
                        $newData = [
                            ...$data,
                            'data' => $this->recursivelyChangePayload($data['data']),
                        ];
                    } else {
                        if (! isset($data['changes']['data'])) {
                            return;
                        }
                        $newData = [
                            'changes' => [
                                ...$data['changes'],
                                'data' => $this->recursivelyChangePayload($data['changes']['data']),
                            ],
                            'original' => [
                                ...$data['original'],
                                'data' => $this->recursivelyChangePayload($data['changes']['data'], $data['original']['data'] ?? []),
                            ],
                        ];
                    }
                    $action->timestamps = false;
                    $action->update(['payload' => $newData]);
                });

            Page::query()
                ->withTrashed()
                ->with(['mapping' => fn ($query) => $query->withTrashed()])
                ->where('type', PageType::ENTITIES->value)
                ->each(function (Page $page) {
                    $design = $page->design;
                    if (! $page->mapping || ! ($design['views'] ?? null)) {
                        return;
                    }
                    $fields = $page->mapping->fields;
                    $multiFields = $fields->filter(fn (Field $field) => $field->type()->is(FieldType::MULTI()));
                    foreach ($design['views'] as &$viewInfo) {
                        $visibleData = $viewInfo['visibleData'] ?? [];
                        if (! $visibleData) {
                            continue;
                        }
                        foreach ($visibleData as &$data) {
                            $formattedId = $data['formattedId'] ?? null;
                            if (! $formattedId) {
                                continue;
                            }
                            // Check if formattedId has a "_" and replace the first one with a "."
                            if ($data['dataType'] === 'FEATURES') {
                                $data['formattedId'] = preg_replace('/[_.]/', '.', $formattedId, 1);

                                continue;
                            }
                            if (! $multiFields->count()) {
                                continue;
                            }
                            if ($data['dataType'] === 'FIELDS') {
                                $multiField = $multiFields->first(fn (MultiField $field) => $field->fields()->firstWhere('id', $formattedId) !== null);
                                if ($multiField) {
                                    $data['formattedId'] = $multiField->id().'>'.$formattedId;
                                }
                            }
                        }
                    }
                    $page->timestamps = false;
                    $page->update(['design' => $design]);
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

    protected function recursivelyChangeValuesToObjects(array $data, FieldCollection $fields): array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if (! $value) {
                continue;
            }

            $field = $fields->find($key);
            if (! $field) {
                continue;
            }

            $isListField = $field->isList();
            if ($isListField) {
                if (! is_array($value) || ! array_is_list($value)) {
                    $value = array_filter([$value]);
                }
                $value = [Field::LIST_VALUE => array_map(function ($item) use ($field) {
                    return $this->normalizeValue($item, $field);
                }, $value)];
            } else {
                $value = $this->normalizeValue($value, $field);
            }
            $newData[$key] = $value;
        }

        return $newData;
    }

    protected function normalizeValue(mixed $value, Field $field): array
    {
        if (! is_array($value) || ! array_key_exists(Field::VALUE, $value)) {
            return [Field::VALUE => $value];
        }

        if ($field->type()->is(FieldType::MULTI())) {
            /** @var \Mappings\Core\Mappings\Fields\Types\MultiField $field */
            $value[Field::VALUE] = $this->recursivelyChangeValuesToObjects($value[Field::VALUE], $field->fields());
        }

        return $value;
    }

    protected function recursivelyChangePayload(array $changes, ?array $original = null): array
    {
        $newData = [];
        $useOriginal = isset($original);
        foreach ($changes as $key => $fieldInfo) {
            if ($useOriginal) {
                $value = $original[$key] ?? null;
            } else {
                $value = $fieldInfo[FieldActionFormatter::FIELD_VALUE];
            }
            if (! $value) {
                continue;
            }

            $mask = $fieldInfo[FieldActionFormatter::OPTION_MASK];
            $isListField = $mask & FieldActionFormatter::IS_LIST;

            if ($isListField) {
                $value = [Field::LIST_VALUE => array_map(function ($item) use ($fieldInfo) {
                    return $this->normalizePayload($item, $fieldInfo);
                }, $value)];
            } else {
                $value = $this->normalizePayload($value, $fieldInfo);
            }
            $newData[$key] = $useOriginal ? $value : [
                ...$fieldInfo,
                FieldActionFormatter::FIELD_VALUE => $value,
            ];
        }

        return $newData;
    }

    protected function normalizePayload(mixed $value, array $fieldInfo): array
    {
        if (! is_array($value) || ! array_key_exists(Field::VALUE, $value)) {
            return [Field::VALUE => $value];
        }

        if ($fieldInfo[FieldActionFormatter::TYPE] === FieldType::MULTI()->value) {
            if ($value === $fieldInfo[FieldActionFormatter::FIELD_VALUE]) {
                $value[Field::VALUE] = $this->recursivelyChangePayload($value[Field::VALUE]);
            } else {
                $value[Field::VALUE] = $this->recursivelyChangePayload($fieldInfo[FieldActionFormatter::FIELD_VALUE][Field::VALUE], $value[Field::VALUE]);
            }
        }

        return $value;
    }
};
