<?php

declare(strict_types=1);

namespace App\Core\Mappings\Features;

use App\Models\Pin;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Drive;
use App\Models\Event;
use App\Models\Mapping;
use App\Models\Calendar;
use App\Models\Document;
use App\Models\LinkList;
use App\Models\Notebook;
use App\Models\Pinboard;
use App\Models\TodoList;
use Illuminate\Support\Str;
use App\Models\Contracts\FeatureList;
use PHPStan\ShouldNotHappenException;
use App\Models\Contracts\FeatureListItem;

enum MappingFeatureType: string
{
    public function newFeature(Mapping $mapping, ?array $options = null): Feature
    {
        /** @var class-string<\App\Core\Mappings\Features\Feature> $class */
        $class = __NAMESPACE__.'\\'.Str::studly(mb_strtolower($this->value)).'Feature';

        return new $class($mapping, $options);
    }

    public static function markableFeatures(): array
    {
        return [
            self::DOCUMENTS,
            self::EVENTS,
            self::PINBOARD,
            self::LINKS,
            self::NOTES,
            self::TODOS,
            self::TIMEKEEPER,
            self::EMAILS,
        ];
    }

    public function toLocaleString(): string
    {
        return trans("hylark.features.{$this->value}");
    }

    /**
     * @template TItem of \App\Models\Contracts\FeatureListItem
     * @template TList of \App\Models\Contracts\FeatureList
     *
     * @param  \App\Models\Contracts\FeatureListItem<TList, TItem>|\App\Models\Contracts\FeatureList<TItem, TList>  $node
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public static function featureForNode(FeatureListItem|FeatureList $node): MappingFeatureType
    {
        return match ($node::class) {
            Todo::class, TodoList::class => self::TODOS,
            Event::class, Calendar::class => self::EVENTS,
            Note::class, Notebook::class => self::NOTES,
            Link::class, LinkList::class => self::LINKS,
            Pin::class, Pinboard::class => self::PINBOARD,
            Document::class, Drive::class => self::DOCUMENTS,
            default => throw new ShouldNotHappenException,
        };
    }

    case DOCUMENTS = 'DOCUMENTS';
    case EVENTS = 'EVENTS';
    case CALENDAR = 'CALENDAR';
    case PINBOARD = 'PINBOARD';
    case LINKS = 'LINKS';
    case COLLABORATION = 'COLLABORATION';
    case COMMENTS = 'COMMENTS';
    case GOALS = 'GOALS';
    case HEALTH = 'HEALTH';
    case NOTES = 'NOTES';
    case PLANNER = 'PLANNER';
    case PRIORITIES = 'PRIORITIES';
    case STATISTICS = 'STATISTICS';
    case TODOS = 'TODOS';
    case TIMEKEEPER = 'TIMEKEEPER';
    case EMAILS = 'EMAILS';
    case FAVORITES = 'FAVORITES';
}
