<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health;

use BenSampo\Enum\Enum;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

trait ChecksEnumColumns
{
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>|TModel  $model
     */
    protected function modelHasInvalidEnums(string|Model $model, string $column): bool
    {
        $enum = $this->getEnumCast($model, $column);

        return $model::query()->whereNotIn($column, $enum)->exists();
    }

    /**
     * @param  class-string<\BenSampo\Enum\Enum<string>>|\BenSampo\Enum\Enum<string>|enum  $enum
     *
     * @phpstan-ignore-next-line The generic type is specified
     */
    protected function tableHasInvalidEnums(string $table, string $column, $enum): bool
    {
        return DB::table($table)->whereNotIn($column, $this->getValuesForEnum($enum))->exists();
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>|TModel  $model
     * @return \Illuminate\Database\Eloquent\Collection<int, TModel>
     */
    protected function getInvalidEnums(string|Model $model, string $column): Collection
    {
        $enum = $this->getEnumCast($model, $column);

        /** @var TModel $model */
        $model = \is_string($model) ? new $model : $model;

        /** @var \Illuminate\Database\Eloquent\Builder<TModel> $query */
        $query = $model->query();

        /** @phpstan-ignore-next-line Should be able to find the method */
        $query->whereNotIn($column, $this->getValuesForEnum($enum));

        return $query->get();
    }

    /**
     * @param  class-string<\BenSampo\Enum\Enum<string>>|\BenSampo\Enum\Enum<string>|enum  $enum
     * @return array<string>
     *
     * @phpstan-ignore-next-line The generic type is specified
     */
    protected function getValuesForEnum($enum): array
    {
        $className = \is_string($enum) ? $enum : $enum::class;
        if (enum_exists($className)) {
            /** @phpstan-ignore-next-line We know it is an enum so cases should work */
            return array_map(fn ($case) => $case->value, $className::cases());
        }

        return $enum::getValues();
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model  $model
     * @return class-string<\BenSampo\Enum\Enum<string>>|enum|null
     *
     * @phpstan-ignore-next-line The generic type is specified
     */
    protected function getEnumCast(string|Model $model, string $column)
    {
        if (\is_string($model)) {
            $model = new $model;
        }
        $enum = $model->getCasts()[$column] ?? null;

        throw_if(
            ! $enum || (! enum_exists($enum) && ! is_subclass_of($enum, Enum::class)),
            new \InvalidArgumentException('The model must cast the column to an enum'),
        );

        /** @var class-string<\BenSampo\Enum\Enum<string>>|enum $enum */
        /** @phpstan-ignore-next-line The generic type is specified */
        return $enum;
    }
}
