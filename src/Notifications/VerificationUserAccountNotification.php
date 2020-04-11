<?php

namespace Knovators\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Knovators\Authentication\Models\UserAccount;

/**
 * Class VerificationUserAccountNotification
 * @package Knovators\Authentication\Notifications
 */
class VerificationUserAccountNotification extends Notification
{

    use Queueable;

    protected $userAccount;

    protected $hashKey;

    /**
     * Create a new notification instance.
     *
     * @param UserAccount $userAccount
     * @param             $hashKey
     */
    public function __construct(UserAccount $userAccount, $hashKey) {
        $this->userAccount = $userAccount;
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
                route('auth.verify.post') . '/?type=email&email=' . $this->userAccount->email . '&key=' . $this->hashKey)
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
