<?php

declare(strict_types=1);

use App\Models\GlobalNotification;
use App\Core\Preferences\NotificationChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can see their notifications', function () {
    $user = createUser();

    /** @var \App\Models\GlobalNotification $globalNotification */
    $globalNotification = GlobalNotification::query()->forceCreate([
        'data' => [
            'header' => 'A new notification',
            'preview' => 'Something new',
            'content' => 'A fantastic new feature',
        ],
        'channel' => NotificationChannel::NEW_FEATURES,
    ]);

    $globalNotification->pushToUsers();

    $this->be($user)->graphQL('
    {
        notifications {
            edges {
                node {
                    header
                    preview
                    content
                }
            }
        }
    }
    ')->assertJson([
        'data' => [
            'notifications' => [
                'edges' => [[
                    'node' => [
                        'header' => 'A new notification',
                        'preview' => 'Something new',
                        'content' => 'A fantastic new feature',
                    ],
                ]],
            ],
        ],
    ]);
});

test('the notifications can be filtered by channel', function () {
    $user = createUser();

    /** @var \App\Models\GlobalNotification $feature */
    $feature = GlobalNotification::query()->forceCreate([
        'data' => [
            'header' => 'A new feature',
            'preview' => 'Something new',
            'content' => 'A fantastic new feature',
        ],
        'channel' => NotificationChannel::NEW_FEATURES,
    ]);

    $feature->pushToUsers();

    /** @var \App\Models\GlobalNotification $tip */
    $tip = GlobalNotification::query()->forceCreate([
        'data' => [
            'header' => 'A tip for you',
            'preview' => 'Have you tried this?',
            'content' => 'It will save you so much time.',
        ],
        'channel' => NotificationChannel::TIPS,
    ]);

    $tip->pushToUsers();

    $this->be($user)->graphQL('
    {
        notifications(channel: TIPS) {
            edges {
                node {
                    header
                }
            }
        }
    }
    ')->assertJsonCount(1, 'data.notifications.edges')->assertJson([
        'data' => [
            'notifications' => [
                'edges' => [[
                    'node' => [
                        'header' => $tip->data['header'],
                    ],
                ]],
            ],
        ],
    ]);

    $this->be($user)->graphQL('
    {
        notifications(channel: NEW_FEATURES) {
            edges {
                node {
                    header
                }
            }
        }
    }
    ')->assertJsonCount(1, 'data.notifications.edges')->assertJson([
        'data' => [
            'notifications' => [
                'edges' => [[
                    'node' => [
                        'header' => $feature->data['header'],
                    ],
                ]],
            ],
        ],
    ]);
});
