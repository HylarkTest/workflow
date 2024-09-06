<?php

use Actions\Core\ActionType;

return [
    'model_with_action' => [
        ActionType::CREATE => 'Created Model called ":subject"',
        ActionType::UPDATE => 'Updated Model called ":subject"',
        ActionType::DELETE => 'Deleted Model called ":subject"',
        ActionType::RESTORE => 'Restored Model called ":subject"',
        'field' => [
            'add' => 'Added :field to Model',
            'change' => 'Changed :field on Model',
            'remove' => 'Removed :field from Model',
        ],
    ],
];
