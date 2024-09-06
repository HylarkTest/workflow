<?php

declare(strict_types=1);

return [
    'oauth' => [
        'denied' => 'You need to give Hylark access to your :provider account in order to integrate.',
        'renew' => 'You must use the account :accountName to renew your integration.',
        'scopes' => 'We only ask for scopes that you request in your integration. Please accept all the scopes or modify your integration.',
    ],
    'image_searches' => [
        'unavailable' => 'We cannot retrieve Google image results right now. Please try again later.',
    ],
    'error_pages' => [
        'title' => 'Hylark: Error',
        '403' => [
            'img_alt' => 'This action is not allowed.',
        ],
        '404' => [
            'img_alt' => 'Marty, the Hylark bird, cannot find your page.',
        ],
        '500' => [
            'message' => 'Whoops, something went wrong.',
            'explanation' => 'An error has occurred and your request was not completed. Our tech team has been notified and will try to resolve any issue as soon as possible. Please try again later or contact us if the problem persists.',
            'img_alt' => 'Marty, the Hylark bird, is extremely apologetic about the error',
        ],
        '503' => [
            'title' => 'Hylark: Down for maintenance',
            'message' => 'Hylark is down for maintenance.',
            'explanation' => 'We are currently performing maintenance on our servers. Please try again later. We apologize for any inconvenience.',
            'img_alt' => 'Marty, the Hylark bird, is extremely apologetic about the error',
        ],
        'default' => [
            'explanation' => 'An error may have occurred. We have been notified. Please try again later or contact us if the problem persists.',
            'message' => 'Whoops, something went wrong...',
            'code' => 'Uh oh',
            'img_alt' => 'Marty, the Hylark bird, is extremely apologetic about the error.',
        ],
    ],
    'invite' => [
        '403' => [
            'token' => 'The invite token is invalid or has expired. Please contact the administrator of the base to request a new invite.',
        ],
        '404' => 'The invite has been deleted. Please contact the administrator of the base to request a new invite.',
        '422' => [
            'limit' => 'You have reached the maximum number of bases allowed per account. To join a new collaborative base you may need to leave one of your current bases.',
        ],
    ],
];
