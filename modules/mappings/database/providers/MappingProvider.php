<?php

declare(strict_types=1);

namespace Mappings\Database\Providers;

use Faker\Provider\Base;
use Illuminate\Support\Arr;
use Mappings\Core\Mappings\Fields\FieldType;

class MappingProvider extends Base
{
    protected array $names = [
        'Contact',
        'Project',
        'Company',
        'Person',
        'Candidate',
    ];

    /**
     * @return mixed
     */
    public function mappingName()
    {
        return static::randomElement($this->names);
    }

    /**
     * @return mixed
     */
    public function mappingFieldType()
    {
        return static::randomElement($this->fields());
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

    public function mapping(): void {}

    /**
     * @return \Mappings\Core\Mappings\Fields\FieldType[]
     */
    protected function fields(): array
    {
        return Arr::where(
            FieldType::getValues(),
            fn ($v) => ! in_array($v, ['FIRST_NAME', 'LAST_NAME', 'NAME', 'MULTI'])
        );
    }
}
