<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use Illuminate\Support\Arr;
use Actions\Core\ActionType;
use Actions\Core\ActionRecorder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class MappingActionRecorder extends ActionRecorder
{
    /**
     * @var \App\Models\Mapping
     */
    protected $model;

    protected ?Model $mappingActionPerformer;

    /**
     * @param  \Illuminate\Database\Eloquent\Model&\App\Models\Mapping  $model
     * @return \Actions\Models\Action|\Actions\Models\Action[]|array|null
     *
     * @throws \Exception
     */
    public function record(Model $model, ?Model $performer = null, bool $force = false): Action|array|null
    {
        $this->mappingActionPerformer = $performer ?: $this->resolveUser($model);
        $this->model = $model;

        $type = $this->getType($model);

        if (! $type->is(ActionType::UPDATE())) {
            return parent::record($model, $this->mappingActionPerformer, $force);
        }

        if (! $this->shouldRecord($model, $this->mappingActionPerformer, $force)) {
            return null;
        }

        return array_merge(
            Arr::wrap($this->createBasicUpdateActions()),
            Arr::wrap($this->createFieldActions() ?? []),
            Arr::wrap($this->createFeatureAction() ?? []),
            Arr::wrap($this->createRelationshipActions() ?? []),
            Arr::wrap($this->createMarkerActions() ?? []),
        );
    }

    /**
     * @param  \App\Models\Mapping|\Illuminate\Database\Eloquent\Model  $model
     */
    public function getType(Model $model): ActionType
    {
        return ActionType::fromValue(parent::getType($model)->value);
    }

    public function createFeatureAction(): ?array
    {
        if (! $this->model->wasChanged('features')) {
            return null;
        }

        /** @var \Illuminate\Support\Collection<int, array> $original */
        $original = $this->model->getOriginal('features')->map->toArray();
        /** @var \Illuminate\Support\Collection<int, array> $new */
        $new = $this->model->features->map->toArray();

        return $this->createActionsForArrayChanges(
            $original,
            $new,
            MappingActionType::ADD_MAPPING_FEATURE(),
            MappingActionType::CHANGE_MAPPING_FEATURE(),
            MappingActionType::REMOVE_MAPPING_FEATURE(),
            $this->config->get('actions.ignore'),
            'val',
            'val',
        );
    }

    protected function createBasicUpdateActions(): array
    {
        $basicFields = ['name', 'singular_name', 'description', 'design'];

        /** @var \Illuminate\Support\Collection<int, \Actions\Models\Action> $actions */
        $actions = collect();

        if ($this->model->wasChanged($basicFields)) {
            $changes = Arr::only($this->model->getChanges(), $basicFields);
            $original = Arr::only($this->model->getRawOriginal(), array_keys($changes));

            if (isset($changes['design'])) {
                $designChanges = $this->model->fromJson($changes['design']);
                $designOriginal = $this->model->fromJson($original['design'] ?? null);

                unset($changes['design'], $original['design']);

                if (isset($designChanges['icon'])) {
                    if ($designOriginal['icon'] !== $designChanges['icon']) {
                        $changes['icon'] = $designChanges['icon'];
                        $original['icon'] = $designOriginal['icon'] ?? null;
                    }
                    unset($designChanges['icon'], $designOriginal['icon']);
                }

                /*
                 * If icon was the only change we don't need to create another action.
                 */
                if ($designChanges && $designChanges !== $designOriginal) {
                    $actions->push(Action::createAction(
                        $this->model,
                        $this->mappingActionPerformer,
                        MappingActionType::CHANGE_MAPPING_DESIGN(),
                        [
                            'changes' => $designChanges,
                            'original' => $designOriginal ?: null,
                        ]
                    ));
                }
            }

            if ($changes) {
                $actions->push(Action::createAction(
                    $this->model,
                    $this->mappingActionPerformer,
                    ActionType::UPDATE(),
                    [
                        'changes' => $changes,
                        'original' => $original,
                    ]
                ));
            }
        }

        return $actions->all();
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    protected function createFieldActions()
    {
        if (! $this->model->wasChanged('fields')) {
            return null;
        }

        /** @var \Mappings\Core\Mappings\Fields\FieldCollection $fields */
        $fields = $this->model->getOriginal('fields');
        /** @var \Illuminate\Support\Collection<int, \Mappings\Core\Mappings\Fields\Field> $original */
        $original = $fields->map->toArray();
        /** @var \Illuminate\Support\Collection<int, \Mappings\Core\Mappings\Fields\Field> $new */
        $new = $this->model->fields->map->toArray();

        return $this->createActionsForArrayChanges(
            $original,
            $new,
            MappingActionType::ADD_MAPPING_FIELD(),
            MappingActionType::CHANGE_MAPPING_FIELD(),
            MappingActionType::REMOVE_MAPPING_FIELD(),
            $this->config->get('actions.ignore'),
        );
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    protected function createRelationshipActions()
    {
        if (! $this->model->wasChanged('relationships')) {
            return null;
        }

        /** @var \Mappings\Core\Mappings\Relationships\RelationshipCollection $relationships */
        $relationships = $this->model->getOriginal('relationships');
        /** @var \Illuminate\Support\Collection<int, \Mappings\Core\Mappings\Relationships\Relationship> $original */
        $original = $relationships->map->toArray();
        /** @var \Illuminate\Support\Collection<int, \Mappings\Core\Mappings\Relationships\Relationship> $new */
        $new = $this->model->relationships->map->toArray();

        return $this->createActionsForArrayChanges(
            $original,
            $new,
            MappingActionType::ADD_MAPPING_RELATIONSHIP(),
            MappingActionType::CHANGE_MAPPING_RELATIONSHIP(),
            MappingActionType::REMOVE_MAPPING_RELATIONSHIP(),
            [...$this->config->get('actions.ignore'), 'inverse']
        );
    }

    /**
     * @return \Actions\Models\Action|\Actions\Models\Action[]|null
     */
    protected function createMarkerActions()
    {
        if (! $this->model->wasChanged('marker_groups')) {
            return null;
        }

        /** @var \App\Core\Mappings\Markers\MappingMarkerGroupCollection|null $markers */
        $markers = $this->model->getOriginal('marker_groups');
        /** @var \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup> $original */
        $original = $markers?->map->toArray() ?? [];
        /** @var \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup> $new */
        $new = $this->model->markerGroups?->map->toArray() ?? [];

        return $this->createActionsForArrayChanges(
            $original,
            $new,
            MappingActionType::ADD_MAPPING_TAG_GROUP(),
            MappingActionType::CHANGE_MAPPING_TAG_GROUP(),
            MappingActionType::REMOVE_MAPPING_TAG_GROUP(),
            [...$this->config->get('actions.ignore'), 'type'],
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $original
     * @param  \Illuminate\Support\Collection<int, mixed>  $new
     */
    protected function createActionsForArrayChanges(
        Collection $original,
        Collection $new,
        ActionType $addedEnum,
        ActionType $changedEnum,
        ActionType $removedEnum,
        array $ignore = [],
        string $idField = 'id',
        string $nameField = 'name',
    ): array {
        $comparator = fn ($a, $b): int => $a[$idField] <=> $b[$idField];

        $added = $new->diffUsing($original, $comparator);
        $removed = $original->diffUsing($new, $comparator);
        $changed = $new->diffUsing($added, $comparator)->diffUsing($original, fn ($a, $b) => ($a <=> $b));

        return $added->map(function ($addedField) use ($addedEnum, $ignore, $nameField) {
            $addedField['name'] = $addedField[$nameField];

            return Action::createAction(
                $this->model,
                $this->mappingActionPerformer,
                $addedEnum,
                Arr::except(array_filter($addedField ?: [], 'filled') ?: [], $ignore),
            );
        })->merge($removed->map(function ($removedField) use ($removedEnum, $nameField) {
            return Action::createAction(
                $this->model,
                $this->mappingActionPerformer,
                $removedEnum,
                ['name' => $removedField[$nameField]],
            );
        }))->merge($changed->map(function ($changedField) use ($original, $changedEnum, $ignore, $idField, $nameField) {
            $originalField = $original->firstWhere($idField, $changedField[$idField]);

            $name = $changedField[$nameField];
            $changes = Arr::except(array_diff_recursive(array_filter($changedField ?: [], 'filled') ?: [], $originalField), $ignore);
            $original = Arr::except(array_intersect_key_recursive(array_filter($originalField ?: [], 'filled') ?: [], $changes), $ignore);

            return Action::createAction(
                $this->model,
                $this->mappingActionPerformer,
                $changedEnum,
                compact('name', 'changes', 'original'),
            );
        }))->all();
    }
}
