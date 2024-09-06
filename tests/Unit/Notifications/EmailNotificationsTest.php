<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Contracts\CustomEmailNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

test('a notification is sent to a users email address by default', function () {
    $user = User::factory()->make();

    NotificationFacade::fake();

    $notification = new TestNotification;
    $user->notify($notification);

    NotificationFacade::assertSentTo(
        $user,
        TestNotification::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routeNotificationFor('mail', $notification) === $user->email;
        }
    );
});

test('a notification can be sent to a custom email address', function () {
    $user = User::factory()->make();
    $customEmail = 'custom@email.com';

    NotificationFacade::fake();

    $notification = new CustomEmailTestNotification($user, $customEmail);
    $user->notify($notification);

    NotificationFacade::assertSentTo(
        $user,
        CustomEmailTestNotification::class,
        function ($notification, $channels, $notifiable) use ($customEmail) {
            return $notifiable->routeNotificationFor('mail', $notification) === $customEmail;
        }
    );
});

class TestNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->markdown('emails.dummy-mail')
            ->subject('subject')
            ->line('body');
    }
}

class CustomEmailTestNotification extends TestNotification implements CustomEmailNotification
{
    public function __construct(
        protected User $user,
        protected string $emailAddress
    ) {
        //
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
