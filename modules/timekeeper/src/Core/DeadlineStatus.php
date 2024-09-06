<?php

declare(strict_types=1);

namespace Timekeeper\Core;

use Illuminate\Support\Carbon;
use Elastic\ScoutDriverPlus\Support\Query;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface;

enum DeadlineStatus: string
{
    public static function getStatus(?Carbon $startAt, ?Carbon $dueBy, ?Carbon $completedAt): self
    {
        $now = now();

        return match (true) {
            $completedAt !== null => self::COMPLETED,
            $dueBy?->isBefore($now) => self::OVERDUE,
            $startAt?->isAfter($now) => self::WAITING_TO_START,
            ($startAt || $dueBy) && (! $startAt || $startAt->isBefore($now)) && (! $dueBy || $dueBy->isAfter($now)) => self::ACTIVE,
            default => self::NO_STATUS,
        };
    }

    public function scopeQuery(Builder $query): Builder
    {
        if ($this !== self::COMPLETED) {
            $query->whereNull('completed_at');
        }

        return match ($this) {
            self::COMPLETED => $query->whereNotNull('completed_at'),
            self::OVERDUE => $query->where('due_by', '<=', now()),
            self::WAITING_TO_START => $query->where('start_at', '>=', now()),
            self::NO_STATUS => $query->whereNull('start_at')->whereNull('due_by'),
            self::ACTIVE => $query->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull('start_at')
                        ->orWhereNotNull('due_by');
                });
                $query->where(function (Builder $query) {
                    $query->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                });
                $query->where(function (Builder $query) {
                    $query->whereNull('due_by')
                        ->orWhere('due_by', '>=', now());
                });
            }),
        };
    }

    public function buildEsQuery(): QueryBuilderInterface
    {
        $query = Query::bool();

        if ($this !== self::COMPLETED) {
            $query->mustNot(Query::exists()->field('completed_at'));
        }
        $must = match ($this) {
            self::COMPLETED => Query::exists()->field('completed_at'),
            self::OVERDUE => Query::range()->field('due_by')->lte(now()),
            self::WAITING_TO_START => Query::range()->field('start_at')->gte(now()),
            self::NO_STATUS => Query::bool()
                ->mustNot(Query::exists()->field('start_at'))
                ->mustNot(Query::exists()->field('due_by')),
            self::ACTIVE => Query::bool()
                ->must(
                    Query::bool()->should(Query::exists()->field('start_at'))
                        ->should(Query::exists()->field('due_by'))
                        ->minimumShouldMatch(1)
                )
                ->must(
                    Query::bool()
                        ->should(Query::bool()->mustNot(Query::exists()->field('start_at')))
                        ->should(Query::range()->field('start_at')->lte(now()))
                        ->minimumShouldMatch(1)
                )
                ->must(
                    Query::bool()
                        ->should(Query::bool()->mustNot(Query::exists()->field('due_by')))
                        ->should(Query::range()->field('due_by')->lte(now()))
                        ->minimumShouldMatch(1)
                )
        };

        return $query->must($must);
    }

    case NO_STATUS = 'NO_STATUS';
    case WAITING_TO_START = 'WAITING_TO_START';
    case ACTIVE = 'ACTIVE';
    case OVERDUE = 'OVERDUE';
    case COMPLETED = 'COMPLETED';
}
