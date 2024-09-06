<?php

declare(strict_types=1);

namespace App\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use LighthouseHelpers\Concerns\HasGlobalId;
use LighthouseHelpers\Contracts\MultipleGraphQLInterfaces;
use Nuwave\Lighthouse\GlobalId\NodeRegistry as BaseNodeRegistry;

class NodeRegistry extends BaseNodeRegistry
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $root
     */
    public function resolveType($root = null, ?AppContext $context = null, ?ResolveInfo $resolveInfo = null): Type
    {
        if ($resolveInfo && $root) {
            $schema = $resolveInfo->schema;
            if ($root instanceof MultipleGraphQLInterfaces) {
                $typeName = $root::resolveType($root);
            } elseif (in_array(HasGlobalId::class, class_uses_recursive($root))) {
                /** @phpstan-ignore-next-line Method exists as it has trait */
                $typeName = $root->typeName();
            }
            if (isset($typeName)) {
                return $schema->getType($typeName) ?? throw new \Exception("Type $typeName not found in schema");
            }
        }

        return parent::resolveType();
    }
}
