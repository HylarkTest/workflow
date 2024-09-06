<?php

declare(strict_types=1);

namespace App\PHPStan;

use App\Models\Base;
use App\Models\Item;
use App\Models\Page;
use App\Core\BaseType;
use Color\ColorFormat;
use PHPStan\Type\Type;
use App\Core\TaskStatus;
use App\Core\Groups\Role;
use PHPStan\Type\NullType;
use Illuminate\Support\Arr;
use PHPStan\Type\ArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\MixedType;
use App\Core\Pages\PageType;
use Documents\Core\FileType;
use Markers\Core\MarkerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use App\Models\BaseUserPivot;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\IntegerType;
use Illuminate\Support\Carbon;
use PHPStan\Type\IterableType;
use PHPStan\Analyser\NameScope;
use App\Models\EmailAddressable;
use PHPStan\Type\TypeCombinator;
use Illuminate\Http\UploadedFile;
use Mappings\Models\CategoryItem;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Scope;
use Timekeeper\Core\DeadlineStatus;
use App\Models\DatabaseNotification;
use PHPStan\PhpDoc\TypeNodeResolver;
use AccountIntegrations\Core\Provider;
use PHPStan\PhpDoc\TypeStringResolver;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\MappingType;
use AccountIntegrations\Core\Todos\Todo;
use AccountIntegrations\Core\Emails\Email;
use App\Core\Mappings\FieldFilterOperator;
use App\Core\Mappings\MarkerFilterOperator;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use AccountIntegrations\Core\Calendar\Event;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Core\Todos\TodoList;
use Mappings\Core\Mappings\Fields\FieldType;
use PHPStan\Type\Constant\ConstantArrayType;
use App\Core\Preferences\NotificationChannel;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Constant\ConstantIntegerType;
use AccountIntegrations\Core\Calendar\Calendar;
use Mappings\Core\Mappings\Fields\SalaryPeriod;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use LighthouseHelpers\Pagination\PaginationResult;
use Mappings\Core\Timestamps\DateTimeStringFormat;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Mappings\Core\Mappings\Fields\AddressFieldName;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class GraphQLTypeResolverExtension implements TypeNodeResolverAwareExtension, TypeNodeResolverExtension
{
    protected const GRAPHQL_PREFIXES = [
        'GArgs',
        'GType',
        'GVal',
    ];

    protected const ScalarTypeMap = [
        'Int' => IntegerType::class,
        'String' => StringType::class,
        'Float' => FloatType::class,
        'Boolean' => BooleanType::class,
        'Email' => StringType::class,
        'DateTime' => Carbon::class,
        'ID' => StringType::class,
        'Color' => StringType::class,
    ];

    protected const ENUM_MAP = [
        'PageType' => PageType::class,
        'MarkerType' => MarkerType::class,
        'FieldType' => FieldType::class,
        'MappingFeatureType' => MappingFeatureType::class,
        'NotificationChannel' => NotificationChannel::class,
        'FieldFilterOperator' => FieldFilterOperator::class,
        'MarkerFilterOperator' => MarkerFilterOperator::class,
        'DeadlineStatus' => DeadlineStatus::class,
        'Role' => Role::class,
        'BaseType' => BaseType::class,
        'TaskStatus' => TaskStatus::class,
        'MarkableType' => '"DOCUMENTS"|"EVENTS"|"PINBOARD"|"LINKS"|"NOTES"|"TODOS"|"TIMEKEEPER"|"EMAILS"',
        'Provider' => Provider::class,
        'Scope' => Scope::class,
        'FileType' => FileType::class,
        'ColorFormat' => ColorFormat::class,
        'MappingType' => MappingType::class,
        'RelationshipType' => RelationshipType::class,
        'AddressFieldName' => AddressFieldName::class,
        'SalaryPeriod' => SalaryPeriod::class,
        'DateTimeStringFormat' => DateTimeStringFormat::class,
    ];

    protected const NODE_MAP = [
        'EntitiesPage' => Page::class,
        'EntityPage' => Page::class,
        'ListPage' => Page::class,
        'Notification' => DatabaseNotification::class,
        'Findable' => 'Finder\GloballySearchable&Illuminate\Database\Eloquent\Model',
        'Item' => Item::class,
        'Node' => Model::class,
        'ExternalEvent' => Event::class,
        'ExternalCalendar' => Calendar::class,
        'ExternalTodo' => Todo::class,
        'ExternalTodoList' => TodoList::class,
        'EmailMessage' => Email::class,
        'Mailbox' => Mailbox::class,
        'EmailAddressAssociation' => EmailAddressable::class,
        'CategoryItem' => CategoryItem::class,
        'IntegrationAccount' => IntegrationAccount::class,
        'BaseEdge' => Base::class,
        'Member' => BaseUserPivot::class,
    ];

    protected const ITEM_INTERFACES = [
        'Item',
        'Markable',
        'Assignable',
        'Findable',
        'Associatable',
    ];

    protected const IGNORE_CONNECTION_TYPES = [
        'EmailConnection',
        'GroupedEmailConnection',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $types;

    /**
     * @var array<string, string[]>
     */
    protected array $possibleTypes;

    private TypeNodeResolver $typeNodeResolver;

    /**
     * @var array<int, array<string, \PHPStan\Type\Type>>
     */
    protected array $typeCache = [];

    public function __construct(protected TypeStringResolver $typeStringResolver)
    {
        /** @phpstan-ignore-next-line */
        $types = json_decode(file_get_contents(__DIR__.'/../frontend/schema.json'), true)['__schema']['types'];
        $this->types = Arr::keyBy($types, 'name');
        /** @phpstan-ignore-next-line */
        $this->possibleTypes = json_decode(file_get_contents(__DIR__.'/../frontend/possibleTypes.json'), true);
        foreach (self::ITEM_INTERFACES as $interface) {
            if (! isset($this->possibleTypes[$interface])) {
                $this->possibleTypes[$interface] = [];
            }
            $this->possibleTypes[$interface][] = 'Item';
        }
    }

    /**
     * @param  array<string, mixed>  $type
     * @return array<string, mixed>|null
     */
    protected function getField(array $type, string $fieldName): ?array
    {
        $fields = $type['kind'] === 'INPUT_OBJECT' ? $type['inputFields'] : $type['fields'];
        foreach ($fields as $field) {
            if ($field['name'] === $fieldName) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $type
     * @return array<string, mixed>|null
     */
    protected function getFieldArgs(array $type, string $fieldName): ?array
    {
        $field = $this->getField($type, $fieldName);

        return $field['args'] ?? null;
    }

    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if (! $typeNode instanceof GenericTypeNode) {
            return null;
        }

        $typeName = $typeNode->type;
        if (! in_array($typeName->name, self::GRAPHQL_PREFIXES, true)) {
            return null;
        }

        $arguments = $typeNode->genericTypes;
        $promiseDepth = 0;
        $lastArg = Arr::last($arguments);
        if ($lastArg instanceof ConstTypeNode) {
            $lastArgType = $this->typeNodeResolver->resolve($lastArg, $nameScope);
            if ($lastArgType instanceof ConstantIntegerType) {
                array_pop($arguments);
                $promiseDepth = $lastArgType->getValue();
            }
        }
        if (count($arguments) !== 2 && count($arguments) !== 1) {
            return null;
        }

        if ($typeName->name === 'GArgs' || $typeName->name === 'GVal') {
            if (count($arguments) === 1) {
                $fieldType = $this->typeNodeResolver->resolve($arguments[0], $nameScope);
                if (! $fieldType instanceof ConstantStringType) {
                    return null;
                }
                foreach (['Query', 'Mutation', 'Subscription'] as $rootType) {
                    $type = $this->types[$rootType];
                    $field = $this->getField($type, $fieldType->getValue());
                    if ($field) {
                        break;
                    }
                }
            } else {
                $objectType = $this->typeNodeResolver->resolve($arguments[0], $nameScope);
                $fieldType = $this->typeNodeResolver->resolve($arguments[1], $nameScope);
                if (! $objectType instanceof ConstantStringType || ! $fieldType instanceof ConstantStringType) {
                    return null;
                }
                $type = $this->types[$objectType->getValue()];
                $field = $this->getField($type, $fieldType->getValue());
            }
            if ($typeName->name === 'GArgs') {
                $args = $field['args'] ?? null;
                if (! $args) {
                    return new ConstantArrayType([], []);
                }

                return $this->createConstantArrayFromFields($args, true);
            }

            if (! $field) {
                return null;
            }

            return $this->getPHPTypeFromGraphQLType($field['type'], [], ($type['kind'] !== 'INPUT_OBJECT'), $promiseDepth);
        }

        if ($typeName->name === 'GType') {
            $objectType = $this->typeNodeResolver->resolve($arguments[0], $nameScope);
            if (! $objectType instanceof ConstantStringType) {
                return null;
            }
            $gqlType = $this->types[$objectType->getValue()];

            return $this->resolveObjectType($gqlType, [], $promiseDepth);
        }
    }

    /**
     * @param  array<string, mixed>  $type
     * @return array<string, mixed>
     */
    protected function getNestedType(array $type): array
    {
        if ($type['kind'] === 'NON_NULL' || $type['kind'] === 'LIST') {
            return $this->getNestedType($type['ofType']);
        }

        return $type;
    }

    /**
     * @param  array<string, mixed>  $type
     * @param  string[]  $parentTypes
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected function resolveObjectType(array $type, array $parentTypes = [], int $promiseDepth = 1): Type
    {
        if (! ($this->typeCache[$promiseDepth][(string) $type['name']] ?? false)) {
            if (isset(self::NODE_MAP[$type['name']])) {
                $nodeClass = self::NODE_MAP[$type['name']];
                $nodeType = class_exists($nodeClass) ? new ObjectType($nodeClass) : $this->typeStringResolver->resolve($nodeClass);
                $this->typeCache[$promiseDepth][(string) $type['name']] = $nodeType;
            } else {
                $objectType = $this->types[$type['name']];
                if (
                    str_ends_with($objectType['name'], 'Connection')
                    && ! in_array($objectType['name'], self::IGNORE_CONNECTION_TYPES, true)
                    && $this->getField($objectType, 'edges')
                    && $this->getField($objectType, 'pageInfo')
                ) {
                    $edgeField = $this->getField($objectType, 'edges');
                    $edgeVal = $this->getNestedType($edgeField['type']);
                    $edgeType = $this->types[$edgeVal['name']];
                    $nodeField = $this->getField($edgeType, 'node');
                    /** @phpstan-ignore-next-line */
                    $nodeVal = $this->getNestedType($nodeField['type']);
                    $nodeType = $this->types[$nodeVal['name']];
                    $resolvedNodeType = $this->resolveObjectType($nodeType, [...$parentTypes, $type['name']], $promiseDepth);
                    $this->typeCache[$promiseDepth][(string) $type['name']] = TypeCombinator::intersect(
                        new IterableType(new MixedType(true), $resolvedNodeType),
                        new ObjectType(PaginationResult::class)
                    );
                } elseif (
                    $objectType['interfaces']
                    && Arr::first($objectType['interfaces'], fn ($interface) => $interface['name'] === 'Node')
                    && class_exists("App\\Models\\{$type['name']}")
                ) {
                    $this->typeCache[$promiseDepth][(string) $type['name']] = new ObjectType("App\\Models\\{$type['name']}");
                } elseif (
                    $objectType['interfaces']
                    && Arr::first($objectType['interfaces'], fn ($interface) => $interface['name'] === 'Item')
                ) {
                    $this->typeCache[$promiseDepth][(string) $type['name']] = new ObjectType(Item::class);
                } else {
                    if ($objectType['kind'] === 'INTERFACE' && ($this->possibleTypes[$type['name']] ?? false)) {
                        $this->typeCache[$promiseDepth][(string) $type['name']] = TypeCombinator::union(
                            ...collect($this->possibleTypes[$type['name']] ?? [])
                                ->map(fn ($possibleType) => $this->resolveObjectType($this->types[$possibleType], $parentTypes, $promiseDepth))
                                ->all()
                        );
                    } else {
                        $fields = $objectType['kind'] === 'INPUT_OBJECT' ? $objectType['inputFields'] : $objectType['fields'];
                        $this->typeCache[$promiseDepth][(string) $type['name']] = $this->createConstantArrayFromFields($fields, $type['kind'] === 'INPUT_OBJECT', [...$parentTypes, $type['name']], $promiseDepth);
                    }
                }
            }
        }

        return $this->typeCache[$promiseDepth][$type['name']];
    }

    protected function wrapResolvedType(Type $type, int $promiseDepth = 1): Type
    {
        $containsNull = TypeCombinator::containsNull($type);
        $promiseType = new GenericObjectType(SyncPromise::class, [$type]);
        $promiseTypes = [];
        for ($i = 0; $i < $promiseDepth; $i++) {
            $promiseTypes[] = $promiseType;
            $subType = $containsNull ? TypeCombinator::addNull($promiseType) : $promiseType;
            $promiseType = new GenericObjectType(SyncPromise::class, [$subType]);
        }

        return TypeCombinator::union(
            $type,
            new ClosureType(returnType: $type),
            ...$promiseTypes
        );
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  string[]  $parentTypes
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected function createConstantArrayFromFields(?array $fields, bool $isInputObject, array $parentTypes = [], int $promiseDepth = 1): ConstantArrayType
    {
        if (! $fields) {
            return new ConstantArrayType([], []);
        }
        $typeBuilder = ConstantArrayTypeBuilder::createEmpty();
        foreach ($fields as $field) {
            $type = $this->getPHPTypeFromGraphQLType($field['type'], $parentTypes, ! $isInputObject, $promiseDepth);
            $typeBuilder->setOffsetValueType(
                new ConstantStringType($field['name']),
                $type,
                $isInputObject && $field['type']['kind'] !== 'NON_NULL'
            );
        }
        /** @var \PHPStan\Type\Constant\ConstantArrayType $type */
        $type = $typeBuilder->getArray();

        return $type;
    }

    /**
     * @param  array<string, mixed>  $gqlType
     * @param  string[]  $parentTypes
     */
    protected function getPHPTypeFromGraphQLType(array $gqlType, array $parentTypes = [], bool $wrapType = true, int $promiseDepth = 1): Type
    {
        if ($gqlType['kind'] === 'NON_NULL') {
            $type = $this->getPHPTypeFromGraphQLType($gqlType['ofType'], $parentTypes, false, $promiseDepth);

            $type = TypeCombinator::removeNull($type);

            return $wrapType ? $this->wrapResolvedType($type, $promiseDepth) : $type;
        }

        if ($gqlType['kind'] === 'LIST') {
            $type = $this->getPHPTypeFromGraphQLType($gqlType['ofType'], $parentTypes, false, $promiseDepth);

            $arrayType = new ArrayType(new IntegerType, $type);
            if ($wrapType) {
                $collectionType = new GenericObjectType(Collection::class, [new IntegerType, $type]);
                // The null type will get removed if it is wrapped by a NON_NULL type
                $arrayType = TypeCombinator::union(new NullType, $arrayType, $collectionType);
            }

            return $wrapType ? $this->wrapResolvedType($arrayType, $promiseDepth) : $arrayType;
        }

        if ($gqlType['kind'] === 'SCALAR') {
            if ($gqlType['name'] === 'Upload') {
                $type = new ObjectType(UploadedFile::class);
            } elseif ($gqlType['name'] === 'JSON') {
                $type = new MixedType(true);
            } else {
                $class = (self::ScalarTypeMap[$gqlType['name']]);
                if (is_a($class, Type::class, true)) {
                    $type = new $class;
                } else {
                    $type = $this->typeStringResolver->resolve($class);
                }
            }
        } elseif (in_array($gqlType['kind'], ['OBJECT', 'INTERFACE', 'INPUT_OBJECT'], true)) {
            // Prevent infinite recursion
            if (in_array($gqlType['name'], $parentTypes, true)) {
                return new MixedType(true);
            }
            $type = $this->resolveObjectType($gqlType, $parentTypes, $promiseDepth);
        } elseif ($gqlType['kind'] === 'UNION') {
            $unionType = $this->types[$gqlType['name']];
            $types = array_map(fn ($type) => $this->getPHPTypeFromGraphQLType($type, $parentTypes, false, $promiseDepth), $unionType['possibleTypes']);
            $type = TypeCombinator::union(...$types);
        } elseif ($gqlType['kind'] === 'ENUM') {
            if (isset(self::ENUM_MAP[$gqlType['name']])) {
                $enumClass = self::ENUM_MAP[$gqlType['name']];
                if (enum_exists($enumClass)) {
                    $type = TypeCombinator::union(
                        ...array_map(fn ($enum) => new ConstantStringType($enum->name), $enumClass::cases())
                    );
                } elseif (class_exists($enumClass)) {
                    $type = new ObjectType($enumClass);
                } else {
                    $type = $this->typeStringResolver->resolve($enumClass);
                }
            } else {
                $enum = $this->types[$gqlType['name']];
                $values = array_map(fn ($value) => new ConstantStringType($value['name']), $enum['enumValues']);
                $type = TypeCombinator::union(...$values);
            }
        } else {
            throw new \Exception($gqlType['kind'].' not implemented yet');
        }

        $type = TypeCombinator::addNull($type);

        return $wrapType ? $this->wrapResolvedType($type, $promiseDepth) : $type;
    }

    public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }
}
