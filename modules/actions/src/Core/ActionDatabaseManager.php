<?php

declare(strict_types=1);

namespace Actions\Core;

use Actions\Models\Action;
use Illuminate\Database\Eloquent\Builder;

class ActionDatabaseManager
{
    /**
     * Update the `is_latest` column for all the actions of the same subject.
     */
    public static function updateIsLatest(Action $latestAction): void
    {
        /*
         * If the action is a CREATE action we don't need to do anything because
         * it will be the first one.
         */
        if ($latestAction->type->is(ActionType::CREATE())) {
            return;
        }

        [$morphType, $morphId] = static::morphColumnNames();

        /*
         * When modifying just the actions for one subject we can have a
         * simpler query that updates all the actions that have `is_latest`
         * set to true and aren't the current action.
         */
        static::newActionQuery()
            ->where([
                $morphId => $latestAction->{$morphId},
                $morphType => $latestAction->{$morphType},
                'is_latest' => true,
                [$latestAction->getKeyName(), '!=', $latestAction->getKey()],
            ])
            ->update(['is_latest' => false]);
    }

    /**
     * Update the `is_latest` column in the `actions` table so only the most
     * recent action for each subject has `is_latest` set to true.
     */
    public static function syncIsLatest(): void
    {
        $latestIds = static::isLatestJoinQuery()
            ->whereNotNull('max_id')
            ->pluck('id');

        static::newActionQuery()
            ->whereKey($latestIds)
            ->update(['is_latest' => 1]);

        static::newActionQuery()
            ->whereKeyNot($latestIds)
            ->update(['is_latest' => 0]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Actions\Models\Action>
     */
    public static function isLatestJoinQuery(): Builder
    {
        [$morphType, $morphId] = static::morphColumnNames();

        /*
         * When modifying the whole table we can group by the subject_id and
         * subject_type and then select the MAX(id) which will give us the
         * most recent. Then use that to find all the actions that aren't
         * the most recent and modify their column (as `is_latest` defaults
         * to true).
         */
        return static::newActionQuery()
            ->leftJoinSub(
                static::newActionQuery()
                    ->selectRaw('MAX(id) AS max_id')
                    ->from('actions')
                    ->groupBy([$morphType, $morphId])
                    ->getQuery(),
                'a',
                'max_id',
                '=',
                'id'
            );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Actions\Models\Action>
     */
    protected static function newActionQuery(): Builder
    {
        $model = new Action;

        /*
         * This method shouldn't update the timestamps of the action.
         */
        $model->timestamps = false;

        return $model->newQuery();
    }

    /**
     * Fetching the column names from the relationship method to avoid
     * duplication, even though it is unlikely to change, it is still
     * good practice.
     */
    protected static function morphColumnNames(): array
    {
        $relation = (new Action)->subject();

        return [$relation->getMorphType(), $relation->getForeignKeyName()];
    }
}
