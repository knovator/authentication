<?php

namespace Knovators\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class ResetPasswordNotification
 * @package Knovators\Authentication\Notifications
 */
class ResetPasswordNotification extends Notification
{

    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param $token
     */
    public function __construct($token) {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        return (new MailMessage)
            ->subject('Forget password request from Textile')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password',
                config('authentication.front_url') . '/reset-password?token=' . $this->token . '&email=' . $notifiable->email)
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            //
        ];
    }
}
