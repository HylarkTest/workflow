<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\Notebook;
use MarkupUtils\MarkupType;

return [
    /*
    |--------------------------------------------------------------------------
    | Note Format
    |--------------------------------------------------------------------------
    |
    | Here you may specify the format for storing notes either as rich text
    | or plain text. The available options are "DELTA", "MARKDOWN", "HTML", "TIPTAP",
    | or "PLAINTEXT".
    |
    */

    'format' => MarkupType::TIPTAP,

    'models' => [
        'note' => Note::class,
        'notebook' => Notebook::class,
    ],
];
