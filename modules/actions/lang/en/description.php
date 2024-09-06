<?php

declare(strict_types=1);

use Actions\Core\ActionType;

return [
    ActionType::CREATE => 'Created ":subject"|":subject" created by :performer',
    ActionType::UPDATE => 'Updated ":subject"|":subject" updated by :performer',
    ActionType::DELETE => 'Deleted ":subject"|":subject" deleted by :performer',
    ActionType::RESTORE => 'Restored ":subject"|":subject" restored by :performer',

    'field' => [
        'add' => 'Added :field',
        'change' => 'Changed :field',
        'remove' => 'Removed :field',
    ],

    'attributes' => [

    ],
];
