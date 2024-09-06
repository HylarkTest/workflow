<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use App\Models\Location;
use App\Core\LocationLevel;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Mappings\Core\Mappings\Fields\Field;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use LighthouseHelpers\Core\ModelBatchLoader;
use Illuminate\Contracts\Translation\Translator;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Mappings\Core\Mappings\Fields\Concerns\HasMultiSelect;
use Mappings\Core\Mappings\Fields\Contracts\MultiSelectField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class LocationField extends Field implements MultiSelectField
{
    use HasMultiSelect;

    public static string $type = 'LOCATION';

    public string $graphQLType = 'Location';

    public string $graphQLInputType = 'ID';

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, protected GlobalId $globalId)
    {
        if (isset($field['options']['countries'])) {
            foreach ($field['options']['countries'] as $key => $location) {
                if (is_numeric($location)) {
                    continue;
                }
                [$type, $id] = $globalId->decode($location);
                if ($type !== 'Location') {
                    throw new \InvalidArgumentException("The location global ID [[$location]] must be a location");
                }
                $field['options']['countries'][$key] = (int) $id;
            }
        }
        parent::__construct($field, $translator);
    }

    public function resolveOptions(): array
    {
        $options = parent::resolveOptions();
        /** @var int|string $location */
        foreach ($options['countries'] ?? [] as $key => $location) {
            if (is_numeric($location)) {
                $options['countries'][$key] = $this->globalId->encode(class_basename(Location::class), $location);
            }
        }

        return $options;
    }

    /**
     * @param  string  $value
     * @param  array  $args
     * @return \GraphQL\Deferred|null
     *
     * @throws \Exception
     */
    public function resolveSuperSingleValue($value, $args)
    {
        return $value ? ModelBatchLoader::instanceFromModel(Location::class)
            ->loadAndResolve($value) : null;
    }

    /**
     * @param  string  $value
     * @param  ?string  $originalValue
     */
    public function serializeSuperSingleValue($value, $originalValue = null): string
    {
        return $this->globalId->decodeID($value);
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        return [
            ...parent::fieldValueRules($isCreate) ?: [],
            function ($attribute, $value, $fail) {
                if (! $value) {
                    return null;
                }
                if (! $this->isMultiSelect()) {
                    $value = [$value];
                }
                /** @var array<int, string> $value */
                $globalIds = collect($value)->map([$this->globalId, 'decode']);
                $ids = $globalIds->pluck(1);
                $types = $globalIds->pluck(0)->unique()->all();

                $query = Location::query()->whereKey($ids);
                if ($this->option('levels')) {
                    $levels = Arr::pluck(LocationLevel::fromNames($this->option('levels')), 'value');
                    $query->whereIn('level', $levels);
                }
                if ($this->option('countries')) {
                    $query->whereIn('country_geoname_id', $this->option('countries'));
                }
                if ($types !== ['Location'] || $query->count() !== \count($ids)) {
                    return $fail($this->translator->get('validation.exists', ['attribute' => "\"$this->name\""]));
                }
            },
        ];
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(parent::optionRules($data), [
            'countries' => 'array',
            'countries.*' => [function ($attribute, $value, $fail) {
                try {
                    $value = $this->globalId->decode($value);
                    [$type, $id] = $value;
                } catch (GlobalIdException) {
                    $type = null;
                    $id = null;
                }
                if ($type !== 'Location' || Location::query()->where('level', LocationLevel::COUNTRY->value)->whereKey($id)->doesntExist()) {
                    return $fail($this->translator->get('validation.exists'));
                }

                return null;
            }],
            'levels' => 'array',
            'levels.*' => [Rule::in(array_map(fn (LocationLevel $level) => $level->name, LocationLevel::cases()))],
            'multiSelect' => ['boolean'],
        ]);
    }

    public function toSearchable(mixed $data): mixed
    {
        if ($data && $this->isList()) {
            return array_map(fn ($item) => $this->getNestedDataValue($item), $this->getNestedDataValue($data) ?: []);
        }

        return $this->getNestedDataValue($data);
    }

    public function toSortable(mixed $data): mixed
    {
        $data = $this->getNestedDataValue($data);
        if ($data && ! \is_array($data)) {
            /** @var \Mappings\Models\CategoryItem|null $item */
            $item = Location::query()->find($data);
            if ($item) {
                return $item->name;
            }
        }

        return null;
    }
}
