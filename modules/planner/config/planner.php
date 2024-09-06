<?php

declare(strict_types=1);

use Planner\Models\Todo;
use Planner\Models\Event;
use Planner\Models\Calendar;
use Planner\Models\TodoList;

return [
    'models' => [
        'todo_list' => TodoList::class,
        'todo' => Todo::class,
        'calendar' => Calendar::class,
        'event' => Event::class,
    ],

    'todos' => [
        'default_color' => '#00ad51',
    ],
];
