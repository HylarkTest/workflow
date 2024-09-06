<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Laravel\Cashier\Subscription;
use Illuminate\Queue\SerializesModels;

class WelcomeToMembership extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $name;

    public string $plan;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Subscription $subscription)
    {
        $this->name = $user->name;
        /** @phpstan-ignore-next-line */
        $this->plan = $subscription->name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail/upgrade.welcome', ['plan' => $this->plan]))
            ->markdown('emails.upgrade');
    }
}
