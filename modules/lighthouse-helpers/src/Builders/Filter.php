<?php

declare(strict_types=1);

namespace LighthouseHelpers\Builders;

use GraphQL\Language\AST\InputValueDefinitionNode;

class Filter
{
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @return \Illuminate\Database\Eloquent\Builder<TModel> $builder
     */
    public function beginsWith($builder, ?string $value, InputValueDefinitionNode $node)
    {
        if (! $value) {
            return $builder;
        }

        return $builder->where($node->name->value, 'like', $value.'%');
    }
}
