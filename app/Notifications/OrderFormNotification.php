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

    public $salesOrder;

    public $attachment;

    /**
     * Create a new notification instance.
     *
     * @param $salesOrder
     * @param $attachment
     */
    public function __construct($salesOrder, $attachment) {
        $this->salesOrder = $salesOrder;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        $mailMessage = (new MailMessage);

        if (!is_null($this->salesOrder->manufacturingCompany)) {
            $mailMessage->from(env('MAIL_FROM_ADDRESS'),
                $this->salesOrder->manufacturingCompany->name);
        }

        return $mailMessage->view(
            'emails.order-form', [
                'customer' => $notifiable,
            ]
        )->subject('Order Form Inquiry')->attach($this->attachment);
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
