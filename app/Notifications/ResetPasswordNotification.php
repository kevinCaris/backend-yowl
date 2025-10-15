<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url') . '/reset/password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
// message personnalisé
        return (new MailMessage)
                    ->subject('Resetting your password')
                    ->line('You are receiving this email because we have received a request to reset your password.')
                    ->action('Resetting your password', $frontendUrl)
                    ->line('If you did not request this, ignore this email..');
    }
}
