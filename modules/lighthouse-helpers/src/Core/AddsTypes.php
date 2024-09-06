<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use LighthouseHelpers\Utils;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\InputObjectType;
use LighthouseHelpers\Pagination\ConnectionField;
use Nuwave\Lighthouse\Pagination\SimplePaginatorField;

/**
 * @phpstan-import-type ObjectConfig from \GraphQL\Type\Definition\ObjectType
 * @phpstan-import-type InputObjectConfig from \GraphQL\Type\Definition\InputObjectType
 *
 * @phpstan-type FieldDefinition array{type: \GraphQL\Type\Definition\Type, resolve?: callable}
 * @phpstan-type ScalarFieldDefinition array{type: \GraphQL\Type\Definition\ScalarType, resolve?: callable}
 * @phpstan-type InputFieldDefinition array{type: \GraphQL\Type\Definition\InputType&\GraphQL\Type\Definition\Type, resolve?: callable}
 *
 * @method ScalarFieldDefinition int(?\Closure $resolver = null, boolean $list = false, boolean $nullable = false, boolean $nullableList = false, ?int $default = null)
 * @method ScalarFieldDefinition string(?\Closure $resolver = null, boolean $list = false, boolean $nullable = false, boolean $nullableList = false, ?string $default = null)
 * @method ScalarFieldDefinition boolean(?\Closure $resolver = null, boolean $list = false, boolean $nullable = false, boolean $nullableList = false, ?bool $default = null)
 * @method ScalarFieldDefinition dateTime(?\Closure $resolver = null, boolean $list = false, boolean $nullable = false, boolean $nullableList = false)
 * @method ScalarFieldDefinition email(?\Closure $resolver = null, boolean $list = false, boolean $nullable = false, boolean $nullableList = false)
 */
trait AddsTypes
{
    /**
     * @phpstan-ignore-next-line
     *
     * @return FieldDefinition
     *
     * @phpstan-ignore-next-line
     */
    public function __call(string $name, array $arguments): array
    {
        $type = match ($name) {
            'id' => Type::id(),
            'int' => Type::int(),
            'string' => Type::string(),
            'boolean' => Type::boolean(),
            default => $this->getType(ucfirst($name)),
        };

        return $this->buildType($type, ...$arguments);
    }

    protected function getRegistry(): TypeRegistry
    {
        /** @phpstan-ignore-next-line This is bound to the correct type */
        return resolve(\Nuwave\Lighthouse\Schema\TypeRegistry::class);
    }

    protected function rootResolver(): \Closure
    {
        return static fn ($root) => $root ?: new \stdClass;
    }

    /**
     * @param  ObjectConfig|\Closure(): ObjectConfig  $options
     */
    protected function registerLazyObject(string $name, \Closure|array $options): void
    {
        /** @phpstan-ignore-next-line InputObjectType is a Type&NamedType */
        $this->registerLazy($name, fn () => new ObjectType([
            'name' => $name,
            ...(\is_callable($options) ? $options() : $options),
        ]));
    }

    /**
     * @param  InputObjectConfig|\Closure(): InputObjectConfig  $options
     */
    protected function registerLazyInput(string $name, \Closure|array $options): void
    {
        /** @phpstan-ignore-next-line InputObjectType is a Type&NamedType */
        $this->registerLazy($name, fn () => new InputObjectType([
            'name' => $name,
            ...(\is_callable($options) ? $options() : $options),
        ]));
    }

    /**
     * @param  (callable(): iterable)|iterable  $fields
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    protected function extendType(string $name, callable|iterable $fields): void
    {
        $type = $this->getRegistry()->get($name);
        \assert($type instanceof ObjectType || $type instanceof InputObjectType, 'Can only extend object or input types');
        $originalFields = $type->config['fields'] ?? [];
        /** @phpstan-ignore-next-line */
        $newFields = static fn () => array_merge(value($originalFields), value($fields));
        if ($type instanceof ObjectType) {
            $this->getRegistry()->overwriteDynamic(new ObjectType([
                ...$type->config,
                'fields' => $newFields,
            ]));
        } else {
            $this->getRegistry()->overwriteDynamic(new InputObjectType([
                ...$type->config,
                'fields' => $newFields,
            ]));
        }
    }

    /**
     * @param  callable(): \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     */
    protected function registerLazy(string $name, callable $type): void
    {
        $this->getRegistry()->registerLazyDynamic($name, $type);
    }

    /**
     * @param  (\GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType)|callable(): (\GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType)  $type
     */
    protected function register((Type&NamedType)|callable $type): void
    {
        /** @var (\GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType) $type */
        $type = value($type);
        $this->getRegistry()->register($type);
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return ScalarFieldDefinition
     */
    protected function id(
        string $typeClass,
        bool $list = false,
        bool $nullable = false,
        bool $nullableList = false,
        mixed $default = null,
        ?\Closure $resolver = null,
        array $args = [],
    ) {
        $idResolver = function (Model $rootValue) use ($resolver, $typeClass) {
            if (! $resolver && method_exists($rootValue, 'globalId')) {
                return $rootValue->globalId();
            }
            $id = $resolver ? $resolver($rootValue) : $rootValue->getKey();

            return resolve(GlobalId::class)->encode($typeClass, $id);
        };

        /* @phpstan-ignore-next-line We know what type it is */
        return $this->buildType(
            Type::id(),
            $list,
            $nullable,
            $nullableList,
            $default,
            $idResolver,
            $args,
        );
    }

    protected function getType(string $name): Type
    {
        return $this->getRegistry()->get($name);
    }

    protected function getInterface(string $name): InterfaceType
    {
        $type = $this->getType($name);
        \assert($type instanceof InterfaceType, 'Expected an interface type');

        return $type;
    }

    /**
     * @return FieldDefinition
     */
    protected function buildType(
        Type|string $type,
        bool $list = false,
        bool $nullable = false,
        bool $nullableList = false,
        mixed $default = null,
        ?\Closure $resolver = null,
        array $args = [],
    ) {
        if (\is_string($type)) {
            $type = $this->getType($type);
        }
        if ($list) {
            if ((! $nullableList) && $type instanceof NullableType) {
                $type = Type::nonNull($type);
            }
            $type = Type::listOf($type);
        }
        if ((! $nullable) && $type instanceof NullableType) {
            $type = Type::nonNull($type);
        }
        /** @var FieldDefinition $field */
        $field = ['type' => $type];
        if ($resolver) {
            $field['resolve'] = $resolver;
        }
        if ($args) {
            $field['args'] = $args;
        }
        if ($default !== null) {
            $field['defaultValue'] = $default;
        }

        return $field;
    }

    /**
     * @return InputFieldDefinition
     */
    protected function buildInputType(
        Type|string $type,
        bool $list = false,
        bool $nullable = false,
        bool $nullableList = false,
        mixed $default = null,
        ?\Closure $resolver = null,
        array $args = [],
    ): array {
        /* @phpstan-ignore-next-line We know what type it is */
        return $this->buildType($type, $list, $nullable, $nullableList, $default, $resolver, $args);
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return FieldDefinition
     *
     * @phpstan-ignore-next-line
     */
    protected function pageInfo(): array
    {
        return $this->buildType(
            'PageInfo',
            resolver: Utils::constructResolver(ConnectionField::class, 'pageInfoResolver'),
        );
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return FieldDefinition
     *
     * @phpstan-ignore-next-line
     */
    protected function simplePageInfo(): array
    {
        return $this->buildType(
            'SimplePaginatorInfo',
            resolver: Utils::constructResolver(SimplePaginatorField::class, 'paginatorInfoResolver'),
        );
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return FieldDefinition
     *
     * @phpstan-ignore-next-line
     */
    protected function edges(string $typeName): array
    {
        return $this->buildType(
            $typeName,
            list: true,
            resolver: Utils::constructResolver(ConnectionField::class, 'edgeResolver'),
        );
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return FieldDefinition
     *
     * @phpstan-ignore-next-line
     */
    protected function paginatorData(string $typeName): array
    {
        return $this->buildType(
            $typeName,
            list: true,
            resolver: Utils::constructResolver(SimplePaginatorField::class, 'dataResolver'),
        );
    }

    protected function registerConnection(string $prefix): void
    {
        $this->registerLazyObject("{$prefix}Connection", fn () => [
            'fields' => [
                'edges' => $this->edges("{$prefix}Edge"),
                'pageInfo' => $this->pageInfo(),
            ],
        ]);
    }

    protected function registerSimplePaginator(string $prefix, array $additionalFields = []): void
    {
        $this->registerLazyObject("{$prefix}Paginator", fn () => [
            'fields' => [
                'data' => $this->paginatorData($prefix),
                'pageInfo' => $this->simplePageInfo(),
                ...$additionalFields,
            ],
        ]);
    }
}
