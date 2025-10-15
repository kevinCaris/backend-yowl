<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmailBase
{
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        // Extraire les paramètres de l'URL Laravel

        $urlParts = parse_url($verificationUrl);
        parse_str($urlParts['query'] ?? '', $queryParams);

        $id = $queryParams['id'] ?? $notifiable->id;
        $hash = $queryParams['hash'] ?? sha1($notifiable->getEmailForVerification());

        $frontendUrl = config('app.frontend_url');

        $frontendVerificationUrl = "{$frontendUrl}/verify-email?id={$id}&hash={$hash}";

        return (new MailMessage)
            ->subject('Vérification de votre adresse email - Gree Logix')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Merci de vous être inscrit sur Gree Logix.')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email.')
            ->action('Vérifier mon email', $frontendVerificationUrl)
            ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.')
            ->salutation('Cordialement, L\'équipe Gree Logix');
    }
}
