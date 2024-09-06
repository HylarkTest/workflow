<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\TypeRegistry as BaseTypeRegistry;

class TypeRegistry extends BaseTypeRegistry
{
    /**
     * @var array<string, (\GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType)|null>
     */
    protected array $explicitTypes = [];

    /**
     * When running on Laravel Octane the type registry is persistent between
     * requests. This is not a problem if the schema is consistent which is
     * usually the case. However, if the schema changes then the cached types
     * might not match up with the types in the AST.
     * Here we override the method that sets the AST and reset the types cache,
     * so it can refetch them from the updated schema.
     * Some types are explicitly set in the registry using the `register` and
     * `overwrite` methods, so we keep these.
     */
    public function setDocumentAST(DocumentAST $documentAST): BaseTypeRegistry
    {
        parent::setDocumentAST($documentAST);

        $this->types = $this->explicitTypes;
        $this->lazyTypes = [];

        return $this;
    }

    public function reset(): void
    {
        $this->setDocumentAST($this->documentAST);
    }

    /**
     * @param  \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function register(Type $type): BaseTypeRegistry
    {
        parent::register($type);

        /** @phpstan-ignore-next-line Doesn't realise that name exists */
        $this->explicitTypes[$type->name] = $type;

        return $this;
    }

    /**
     * @param  \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     */
    public function overwrite(Type $type): BaseTypeRegistry
    {
        parent::overwrite($type);

        /** @phpstan-ignore-next-line Doesn't realise that name exists */
        $this->explicitTypes[$type->name] = $type;

        return $this;
    }

    /**
     * @param  \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     * @return $this
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function registerDynamic(Type $type): self
    {
        parent::register($type);

        return $this;
    }

    /**
     * @param  callable(): \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     * @return $this
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function registerLazyDynamic(string $name, callable $type): self
    {
        parent::registerLazy($name, $type);

        return $this;
    }

    /**
     * @param  \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     * @return $this
     */
    public function overwriteDynamic(Type $type): self
    {
        parent::overwrite($type);

        return $this;
    }

    /**
     * @param  callable(): \GraphQL\Type\Definition\Type&\GraphQL\Type\Definition\NamedType  $type
     * @return $this
     */
    public function overwriteLazyDynamic(string $name, callable $type): self
    {
        parent::overwriteLazy($name, $type);

        return $this;
    }
}
