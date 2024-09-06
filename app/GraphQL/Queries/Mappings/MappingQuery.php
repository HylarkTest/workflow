<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Mappings;

use App\Models\Mapping;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use BenSampo\Enum\Rules\Enum;
use Illuminate\Support\Collection;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\FieldType;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MappingQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param  array{first: int, after?: string, name: string, type: \Mappings\Core\Mappings\MappingType, spaceId: int}  $args
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $base = $context->base();

        $query = $base->mappings();

        /** @phpstan-ignore-next-line Not sure why this doesn't work */
        if ($args['name'] ?? null) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        /** @phpstan-ignore-next-line Not sure why this doesn't work */
        if ($args['type'] ?? null) {
            $query->where('type', $args['type']);
        }

        /** @phpstan-ignore-next-line Not sure why this doesn't work */
        if ($args['spaceId'] ?? null) {
            $query->where('space_id', $args['spaceId']);
        }

        return $this->paginateQuery($query, $args);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Mapping
    {
        $base = $context->base();

        if (! ($args['id'] ?? false)) {
            if ($args['itemId'] ?? false) {
                $id = $base->items()->findOrFail($args['itemId'])->mapping_id;
            } else {
                throw new ModelNotFoundException(Mapping::class);
            }
        } else {
            $id = $args['id'];
        }

        return $base->mappings()->findOrFail($id);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'name',
            'singularName',
            'apiName',
            'singularApiName',
            'type',
            'description',
            'features',
        ]);

        $base = $context->base();

        $fields = $args['input']['fields'] ?? [];
        $rules = [];
        $attributes = [];
        foreach ($fields as $index => $fieldArgs) {
            $field = resolve(FieldType::fieldClass($fieldArgs['type']), ['field' => []]);
            $rules = array_merge($rules, $this->fieldRules($field, $fieldArgs, $index, $fields));

            $attributes = array_merge_recursive(
                trans('mappings::validation.attributes'),
                $field->optionAttributes($args)
            );
        }

        $fields = $this->validate(
            $args,
            [
                'input.fields' => function (string $attribute, array $value, \Closure $fail) use ($base) {
                    if (! $base->accountLimits()->canCreateMappingWithFields($value)) {
                        $fail(trans('validation.max.array', [
                            'max' => $base->accountLimits()->getFieldLimit(),
                        ]));
                    }
                },
                'input.fields.*.id' => '',
                ...$rules,
            ],
            $resolveInfo,
            [],
            Arr::dot($attributes)
        )['input']['fields'];

        $fields[0]['type'] = FieldType::SYSTEM_NAME();

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['input']['spaceId']);

        /** @var \App\Models\Mapping $mapping */
        $mapping = $space->mappings()->create($data);

        foreach ($fields as $field) {
            $mapping->addField($field);
        }

        return $this->mutationResponse(200, 'Mapping was created successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'name',
            'singularName',
            'apiName',
            'singularApiName',
            'description',
            'features',
        ]);

        $base = $context->base();

        /** @var \App\Models\Mapping $mapping */
        $mapping = $base->mappings()->findOrFail($args['input']['id']);

        if (isset($args['input']['markerGroups'])) {
            /** @var \Illuminate\Support\Collection<int, array> $newMarkerGroups */
            /** @phpstan-ignore-next-line */
            $newMarkerGroups = collect($args['input']['markerGroups'])->keyBy('group');
            /** @var \Illuminate\Support\Collection<int, \App\Core\Mappings\Markers\MappingMarkerGroup> $existingMarkerGroups */
            $existingMarkerGroups = $mapping->markerGroups?->keyBy('group') ?? collect();
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\MarkerGroup> $allMarkerGroups */
            $allMarkerGroups = $base->markerGroups()->findOrFail($newMarkerGroups->keys())->keyBy('id');

            $existingMarkerGroups->filter(fn ($existingGroup) => ! $newMarkerGroups->has($existingGroup->group))
                ->each(function ($existingGroup) use ($mapping) {
                    $mapping->validateRemovingMarkerGroup($existingGroup, 'input.usedByMappings');
                });

            foreach ($existingMarkerGroups as $existingGroup) {
                if (! $newMarkerGroups->has($existingGroup->group)) {
                    $mapping->removeMarkerGroup($existingGroup->id());
                }
            }
            foreach ($newMarkerGroups as $markerGroup) {
                if (! $existingMarkerGroups->has($markerGroup['group'])) {
                    /** @phpstan-ignore-next-line We guarantee it exists with the findOrFail above */
                    $mapping->addMarkerGroup($allMarkerGroups->get($markerGroup['group']));
                }
            }
        }

        $mapping->update($data);

        return $this->mutationResponse(200, 'Mapping was updated successfully', [
            'mapping' => $mapping,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $mapping = $base->mappings()->findOrFail($args['input']['id']);

        $mapping->delete();

        return $this->mutationResponse(200, 'Mapping was deleted successfully');
    }

    protected function fieldRules(Field $field, array $data, int $index, array $fields): array
    {
        $optionRules = $field->optionRules($data);
        $fieldCollection = collect($fields);
        $fieldCollection->splice($index, 1);

        return array_merge(
            [
                "input.fields.$index.id" => ['string', 'max:20', 'filled', function ($attribute, $value, $fail) use ($fieldCollection) {
                    if ($fieldCollection->pluck('id')->contains($value)) {
                        $fail(trans('validation.unique'));
                    }
                }],
                "input.fields.$index.apiName" => ['string', 'max:'.Field::MAX_NAME_LENGTH, 'filled', 'api_name', function ($attribute, $value, $fail) use ($fieldCollection) {
                    if ($fieldCollection->pluck('apiName')->contains($value)) {
                        $fail(trans('validation.unique'));
                    }
                }],
                "input.fields.$index.name" => ['string', 'max:50', 'filled', function ($attribute, $value, $fail) use ($fieldCollection) {
                    /** @var \Illuminate\Support\Collection<int, string> $names */
                    $names = $fieldCollection->pluck('name');
                    if ($names->map(fn (string $value) => mb_strtolower($value))->contains(mb_strtolower($value))) {
                        $fail(trans('validation.unique'));
                    }
                }],
                "input.fields.$index.type" => [new Enum(FieldType::class), $index === 0 ? 'in:NAME,SYSTEM_NAME' : 'not_in:SYSTEM_NAME'],
                "input.fields.$index.meta" => [],
                "input.fields.$index.section" => 'nullable|string',
            ],
            Collection::make($optionRules)->mapWithKeys(fn ($ruleSet, $key) => ["input.fields.$index.options.$key" => $ruleSet])->all()
        );
    }
}
