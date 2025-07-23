<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class CustomResetPassword extends ResetPasswordNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        parent::__construct($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // If you want to customize the subject, greeting, lines, etc.
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('messages.password-reset.subject'))
            ->greeting(__('messages.password-reset.greeting'))
            ->line(__('messages.password-reset.line1'))
            ->action(__('messages.password-reset.action'), url(trim(config('app.url'), '/') . route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset()
            ], false)))
            ->line(__('messages.password-reset.line2'))
            ->salutation(__('messages.password-reset.salutation') . "\n\n" . config('app.name'));
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
