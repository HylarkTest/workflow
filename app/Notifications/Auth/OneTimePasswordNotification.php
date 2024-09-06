<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use App\Models\User;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Core\IPLocation\Location;
use App\Core\IPLocation\Position;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Contracts\CustomEmailNotification;

class OneTimePasswordNotification extends Notification implements CustomEmailNotification
{
    protected Carbon $time;

    public function __construct(public string $code, protected User $user, protected Request $request, protected ?string $emailAddress = null)
    {
        $this->time = now();
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?? $this->user->email;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $agent = new Agent;
        $agent->setUserAgent($this->request->userAgent());

        $ipLocation = resolve(Location::class);

        /** @var \App\Core\IPLocation\Position|false $location */
        $location = $ipLocation->get($this->request->ip());

        if ($location instanceof Position && $location->timezone) {
            $this->time->setTimezone($location->timezone);
        }

        /** @var string $subject */
        $subject = __('mail/oneTimePassword.subject');

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.one-time-password', [
                'name' => $this->user->name,
                'code' => $this->code,
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'cityName' => $location ? $location->cityName : null,
                'countryName' => $location ? $location->countryName : null,
                'time' => $this->time,
                'ip' => $this->request->ip(),
            ]);
    }
}
