<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class OrderFormNotification
 * @package App\Notifications
 */
class OrderFormNotification extends Notification
{

    use Queueable;

    public $companyName;

    public $attachment;

    /**
     * Create a new notification instance.
     *
     * @param $companyName
     * @param $attachment
     */
    public function __construct($companyName, $attachment) {
        $this->companyName = $companyName;
        $this->attachment = $attachment;
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
        $mailMessage = (new MailMessage)->from(env('MAIL_FROM_ADDRESS'), $this->companyName);

        return $mailMessage->view(
            'emails.order-form', [
                'customer' => $notifiable,
                'companyName'    => $this->companyName
            ]
        )->subject('Sales Order Form')->attach($this->attachment);
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
