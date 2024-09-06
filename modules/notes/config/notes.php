<?php

declare(strict_types=1);

use Notes\Models\Note;
use Notes\Models\Notebook;
use MarkupUtils\MarkupType;

return [
    /*
    |--------------------------------------------------------------------------
    | Note Format
    |--------------------------------------------------------------------------
    |
    | Here you may specify the format for storing notes either as rich text
    | or plain text. The available options are "DELTA", "MARKDOWN", "HTML",
    | or "PLAINTEXT".
    |
    */

    'format' => MarkupType::PLAINTEXT,

    'models' => [
        'note' => Note::class,
        'notebook' => Notebook::class,
    ],
];
