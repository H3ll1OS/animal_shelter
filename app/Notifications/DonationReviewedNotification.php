<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $status,
        public readonly ?string $rejectionReason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->status === 'Verified') {
            return (new MailMessage)
                ->subject('Donation Verified!')
                ->greeting('Hello,')
                ->line('Your donation has been successfully reviewed and verified by our shelter staff. Thank you for supporting our animal shelter and helping us care for animals in need!');
        }

        return (new MailMessage)
            ->subject('Donation Requires Attention')
            ->greeting('Hello,')
            ->line('Your donation submission could not be verified.')
            ->line('Reason: '.$this->rejectionReason)
            ->line('Please review the information and contact our shelter staff if you need assistance.');
    }
}
