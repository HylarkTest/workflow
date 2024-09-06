<?php

declare(strict_types=1);

namespace Mappings\Models\Concerns;

use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Trait DefinesGraphQLTypes
 *
 * @property string $name
 * @property string $api_name
 * @property string $singular_name
 * @property string $api_singular_name
 * @property string $graphql_type
 * @property string $graphql_single_field
 * @property string $graphql_many_field
 *
 * @mixin Model
 */
trait DefinesGraphQLTypes
{
    /**
     * @var string[]
     */
    protected array $uniqueColumns = [
        'api_name',
        'api_singular_name',
    ];

    protected string $generatedApiSingularName;

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function name(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes = []): array {
            $toSet = ['name' => $value];
            $this->attributes['name'] = $value;

            if (! isset($attributes['api_name'])) {
                $toSet['api_name'] = Utils::generateGraphQLType($value);
                $this->attributes['apiName'] = $toSet['api_name'];
            }

            if (! isset($attributes['singular_name'])) {
                $singular = Str::singular($value);
                if ($singular === $value) {
                    $singular = "$singular Item";
                }
                $setter = $this->singularName()->set;
                /** @phpstan-ignore-next-line Setter accepts two parameters */
                $toSet = array_merge($toSet, $setter($singular, array_merge($attributes, $toSet)));
            }

            return $toSet;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function singularName(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes = []): array {
            $toSet = ['singular_name' => $value];
            if (
                ! isset($attributes['api_singular_name'])
                || $attributes['api_singular_name'] === ($this->generatedApiSingularName ?? new \stdClass)
            ) {
                $this->generatedApiSingularName = Utils::generateGraphQLType($value);
                $setter = $this->apiSingularName()->set;
                /** @phpstan-ignore-next-line Setter accepts two parameters */
                $toSet['api_singular_name'] = $setter($this->generatedApiSingularName, array_merge($attributes, $toSet));
            }

            return $toSet;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function apiSingularName(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes = []): string {
            if ($value === $attributes['api_name']) {
                $value .= 'Item';
            }

            return $value;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function graphqlType(): Attribute
    {
        return Attribute::get(fn (): string => ucfirst($this->api_singular_name));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function graphqlSingleField(): Attribute
    {
        return Attribute::get(fn (): string => lcfirst($this->api_singular_name));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function graphqlManyField(): Attribute
    {
        return Attribute::get(fn (): string => lcfirst($this->api_name));
    }
}
