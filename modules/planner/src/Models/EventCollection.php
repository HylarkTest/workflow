<?php

declare(strict_types=1);

namespace Planner\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @template TKey of array-key
 *
 * @extends \Illuminate\Database\Eloquent\Collection<TKey, \Planner\Models\Event>
 */
class EventCollection extends Collection
{
    /**
     * If no $to date is passed then we only get the first instance after the
     * from date.
     *
     * @return \Planner\Models\EventCollection<int>
     */
    public function expandRecurringEvents(Carbon $from, ?Carbon $to, bool $sort = true): self
    {
        $expandedCollection = new self;

        $this->each(function (Event $event) use ($expandedCollection, $from, $to) {
            $eventAtTz = $event->newInstanceAtTz();
            $toAtTz = $to ? (clone $to)->setTimezone($event->timezone) : null;
            $fromAtTz = (clone $from)->setTimezone($event->timezone);
            $iterator = $eventAtTz->getRecurrenceIterator();
            if (! $iterator && $this->matchesRange($eventAtTz, $fromAtTz, $toAtTz)) {
                $expandedCollection->push($event);
            } elseif ($iterator) {
                $eventAtTz->isInstance = true;
                $diff = $event->end_at->diff($event->start_at, true);
                while ($eventAtTz && $iterator->valid() && (! $toAtTz || $toAtTz->greaterThan($iterator->current()))) {
                    if ($this->matchesRange($eventAtTz, $fromAtTz, $toAtTz)) {
                        $eventAtUtc = $eventAtTz->newInstanceAtTz('UTC', true);
                        $expandedCollection->push($eventAtUtc);
                        if (! $toAtTz) {
                            break;
                        }
                    }
                    $iterator->next();
                    if ($newDate = $iterator->current()) {
                        $nextOccurrence = clone $eventAtTz;

                        $nextOccurrence->start_at = $newDate;
                        $nextOccurrence->end_at = $newDate->add($diff);
                    }
                    $eventAtTz = $nextOccurrence ?? null;
                }
            }
        });

        if ($sort) {
            return $expandedCollection->sortBy('start_at')->values();
        }

        return $expandedCollection;
    }

    protected function matchesRange(Event $event, Carbon $from, ?Carbon $to): bool
    {
        if ($to) {
            return $event->intersectsRange($from, $to);
        }

        return $event->endsAfter($from);
    }
}
