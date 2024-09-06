<?php

declare(strict_types=1);

namespace Actions\GraphQL\Builders;

use Actions\Core\ActionType;
use LighthouseHelpers\Utils;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class ActionsFilter
{
    protected GlobalId $globalId;

    protected array $classMap = [];

    /**
     * ActionsFilter constructor.
     */
    public function __construct(GlobalId $globalId)
    {
        $this->globalId = $globalId;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>  $builder
     * @return \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>
     */
    public function createdBy(Builder|MorphOneOrMany $builder, ?array $performers): Builder|MorphOneOrMany
    {
        return $this->filterActionsByUsersWhere(
            $builder, $performers, 'type', ActionType::CREATE()
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>  $builder
     * @return \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>
     */
    public function lastUpdatedBy(Builder|MorphOneOrMany $builder, ?array $performers): Builder|MorphOneOrMany
    {
        return $this->filterActionsByUsersWhere(
            $builder, $performers, 'is_latest', true
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Actions\Models\Action>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<\Actions\Models\Action>  $builder
     * @return \Illuminate\Database\Eloquent\Builder<\Actions\Models\Action>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<\Actions\Models\Action>
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function performedBy(Builder|MorphOneOrMany $builder, array $performers): Builder|MorphOneOrMany
    {
        $groupedIds = $this->expandGlobalIds($performers);

        $types = array_keys($groupedIds);

        return $builder->whereHasMorph(
            'performer',
            $types,
            fn (Builder $query, $type) => $query->whereKey($groupedIds[$type])
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>  $builder
     * @return \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TModel>
     */
    protected function filterActionsByUsersWhere(Builder|MorphOneOrMany $builder, ?array $performers, string $column, mixed $value): Builder|MorphOneOrMany
    {
        if ($performers === null) {
            return $builder;
        }

        return $builder->whereHas('actions', function (Builder $query) use (
            $performers, $column, $value
        ) {
            return $this->performedBy($query, $performers)->where($column, $value);
        });
    }

    /**
     * Decode global ids and group the ids by the model class.
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    protected function expandGlobalIds(array $ids): array
    {
        $groupedIds = [];

        foreach ($ids as $globalId) {
            [$type, $id] = $this->globalId->decode($globalId);

            $class = Utils::namespaceModelClass($type);

            throw_if(! $class, GlobalIdException::class, "Could not find an entity with the global ID $globalId");

            if (isset($groupedIds[$class])) {
                $groupedIds[$class][] = $id;
            } else {
                $groupedIds[$class] = [$id];
            }
        }

        return $groupedIds;
    }
}
