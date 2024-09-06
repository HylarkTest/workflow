<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;
use Illuminate\Support\Arr;
use Mappings\Core\Mappings\Fields\FieldType;
use App\Core\Mappings\Features\MappingFeatureType;

class MappingProvider extends Base
{
    protected array $names = [
        'Contact',
        'Project',
        'Company',
        'Person',
        'Candidate',
    ];

    public function mappingName()
    {
        return static::randomElement($this->names);
    }

    public function mappingFieldType()
    {
        return static::randomElement($this->fields());
    }

    public function mappingFeatureType()
    {
        return static::randomElement($this->features());
    }

    public function mappingField(): array
    {
        return [
            'name' => $this->generator->word,
            'type' => $this->mappingFieldType(),
            'options' => [],
            'created_at' => (string) now(),
            'updated_at' => (string) now(),
        ];
    }

    public function mappingFeature(): array
    {
        return [
            'type' => $this->mappingFeatureType(),
        ];
    }

    public function mapping(): void {}

    /**
     * @return \Mappings\Core\Mappings\Fields\FieldType[]
     */
    protected function fields(): array
    {
        return Arr::where(
            FieldType::getValues(),
            fn ($v) => ! \in_array($v, ['SYSTEM_NAME', 'FIRST_NAME', 'LAST_NAME', 'NAME', 'MULTI', 'SELECT'], true)
        );
    }

    /**
     * @return \App\Core\Mappings\Features\MappingFeatureType[]
     */
    protected function features(): array
    {
        return MappingFeatureType::cases();
    }
}
