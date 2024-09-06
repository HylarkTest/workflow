<?php

declare(strict_types=1);

namespace App\Core\Pages;

use App\Models\Pin;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Drive;
use App\Models\Event;
use App\Models\Calendar;
use App\Models\Document;
use App\Models\LinkList;
use App\Models\Notebook;
use App\Models\Pinboard;
use App\Models\TodoList;

enum PageType: string
{
    public static function entityTypes(): array
    {
        return [PageType::ENTITIES, PageType::ENTITY];
    }

    public static function listTypes(): array
    {
        return [
            PageType::LINKS,
            PageType::CALENDAR,
            PageType::TODOS,
            PageType::DOCUMENTS,
            PageType::PINBOARD,
            PageType::NOTES,
        ];
    }

    public static function fromClass(string $class): self
    {
        /** @phpstan-ignore-next-line  */
        return match ($class) {
            Notebook::class, Note::class => self::NOTES,
            Pinboard::class, Pin::class => self::PINBOARD,
            LinkList::class, Link::class => self::LINKS,
            TodoList::class, Todo::class => self::TODOS,
            Calendar::class, Event::class => self::CALENDAR,
            Drive::class, Document::class => self::DOCUMENTS,
        };
    }

    case ENTITY = 'ENTITY';
    case ENTITIES = 'ENTITIES';
    case LINKS = 'LINKS';
    case CALENDAR = 'CALENDAR';
    case TODOS = 'TODOS';
    case DOCUMENTS = 'DOCUMENTS';
    case PINBOARD = 'PINBOARD';
    case NOTES = 'NOTES';
    case EDITABLE_DOCUMENT = 'EDITABLE_DOCUMENT';
    case FREEDOC = 'FREEDOC';
}
