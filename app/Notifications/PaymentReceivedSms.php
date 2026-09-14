<?php

namespace App\Notifications;

use App\Models\TravelPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\TwilioMessage;
use Illuminate\Notifications\Notification;

/**
 * SMS notification fired when a customer payment is received.
 */
class PaymentReceivedSms extends Notification
{
    use Queueable;

    public function __construct(public TravelPayment $payment) {}

    public function via($notifiable): array
    {
        return ['twilio'];
    }

    public function toTwilio($notifiable): TwilioMessage
    {
        $amount = number_format((float) $this->payment->amount, 2);
        $currency = $this->payment->currency ?? '';
        $group = $this->payment->bookingGroup?->group_name ?? 'your booking';

        return (new TwilioMessage())
            ->content("Payment of {$currency} {$amount} received for {$group}. Thank you. – ZapFlow");
    }
}
