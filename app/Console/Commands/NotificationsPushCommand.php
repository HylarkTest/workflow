<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GlobalNotification;

class NotificationsPushCommand extends Command
{
    protected $signature = 'notifications:push';

    protected $description = 'Ensure any outstanding global notifications are pushed';

    public function handle(): int
    {
        GlobalNotification::query()
            ->whereNull('pushed_at')
            ->where('will_automatically_push_at', '<', now()->subMinutes(10))
            ->eachById(function (GlobalNotification $notification) {
                $notification->pushToUsers();
            });

        return 0;
    }
}
