<?php

declare(strict_types=1);

use App\Models\Todo;
use App\Models\Event;
use App\Models\Calendar;
use App\Models\TodoList;

return [
    'models' => [
        'todo_list' => TodoList::class,
        'todo' => Todo::class,
        'calendar' => Calendar::class,
        'event' => Event::class,
    ],

    'todos' => [
        'default_color' => '#4CD453',
    ],
    'events' => [
        'default_color' => '#4CD453',
    ],
];
