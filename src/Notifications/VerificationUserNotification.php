<?php

namespace Knovators\Authentication\Notifications;

use Knovators\Authentication\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class VerificationUserNotification
 * @package Knovators\Authentication\Notifications
 */
class VerificationUserNotification extends Notification
{

    use Queueable;

    protected $user;

    protected $hashKey;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param      $hashKey
     */
    public function __construct(User $user, $hashKey) {
        $this->user = $user;
        $this->hashKey = $hashKey;
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
     * @return MailMessage
     */
    public function toMail($notifiable) {
        return (new MailMessage)
            ->subject('Account verification - ' . config('app.name'))
            ->greeting('Welcome to ' . config('app.name'))
            ->line('Thanks for signing up for ' . config('app.name') . '! We\'re excited to help you learn. Please verify your account.')
            ->action('VERIFY YOUR ACCOUNT NOW!',
                route('auth.verify.post') . '/?type=email&email=' . $this->user->email . '&key=' . $this->hashKey)
            ->line('Thank you for using our application!');
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
