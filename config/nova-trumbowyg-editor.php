<?php

declare(strict_types=1);

return [
    'btns' => [
        ['undo', 'redo'], // Only supported in Blink browsers
        ['formatting'],
        ['strong', 'em', 'del'],
        ['foreColor', 'backColor', 'fontsize'],
        ['superscript', 'subscript'],
        ['link'],
        ['insertImage', 'upload', 'emoji'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['horizontalRule'],
        ['noembed', 'articleSearch'],
    ],
    'plugins' => [
        'upload' => [
            'serverPath' => '/nova-vendor/hylark/article-content/upload',
            'fileFieldName' => 'image',
            'urlPropertyName' => 'location',
            'statusPropertyName' => 'location',
        ],
        'articleSearch' => [],
    ],
];
