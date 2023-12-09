<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteNotification extends Notification
{
    use Queueable;

    protected $notification_url;
    protected $inviter_username;

    /**
     * Create a new notification instance.
     */
    public function __construct($notification_url, $inviter_username)
    {
        $this->notification_url = $notification_url;
        $this->inviter_username = $inviter_username;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You\'ve been invited to ' . config('app.name') . '!')
            ->line($this->inviter_username . ' invited you to be friends on ' . config('app.name') . '. Click the link below to sign up and accept their friend request!')
            ->action('Join '. config('app.name'), $this->notification_url)
            ->line('Thank you for using ' . config('app.name') . '!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
