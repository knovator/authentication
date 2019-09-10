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

    public $module;

    /**
     * Create a new notification instance.
     *
     * @param $companyName
     * @param $attachment
     * @param $module
     */
    public function __construct($companyName, $attachment, $module) {
        $this->companyName = $companyName;
        $this->attachment = $attachment;
        $this->module = $module;
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
        if ($this->module == 'yarn') {
            $subject = 'SO Yarn Tax invoice';
        } else {
            $subject = 'SO Order Form';
        }

        return $mailMessage->view(
            'emails.order-form', [
                'customer'    => $notifiable,
                'companyName' => $this->companyName,
                'module'      => $this->module,
            ]
        )->subject($subject)->attach($this->attachment);
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
