<?php

declare(strict_types=1);

namespace App\Console\Commands\Registration;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\CompleteRegistration;

class CompleteEmailSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration:complete-email:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder to users to complete their registration';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Email users who have not completed registration within 21 days of signing up
        $users = User::query()
            ->whereBetween('created_at', [now()->subDays(21)->startOfDay(), now()->subDays(21)->endOfDay()])
            ->whereNull('finished_registration_at')
            ->each(function (User $user) {
                $user->notify(new CompleteRegistration);
            });
    }
}
