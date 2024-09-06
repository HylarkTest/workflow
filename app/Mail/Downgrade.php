<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Downgrade extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $name;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->name = $user->name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail/downgrade.subject'))
            ->markdown('emails.downgrade');
    }
}
