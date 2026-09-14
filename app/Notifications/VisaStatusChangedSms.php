<?php

namespace App\Notifications;

use App\Models\Passenger;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\TwilioMessage;
use Illuminate\Notifications\Notification;

/**
 * SMS notification fired when a passenger's visa status changes.
 * Only sent if the user has a mobile_no and the company has Twilio enabled.
 */
class VisaStatusChangedSms extends Notification
{
    use Queueable;

    public function __construct(
        public Passenger $passenger,
        public string $newStatus,
    ) {}

    /**
     * Only deliver via Twilio (sms).
     */
    public function via($notifiable): array
    {
        return ['twilio'];
    }

    public function toTwilio($notifiable): TwilioMessage
    {
        $readable = ucwords(str_replace('_', ' ', $this->newStatus));
        $name = $this->passenger->first_name ?? 'Passenger';

        return (new TwilioMessage())
            ->content("Visa update for {$name}: status is now '{$readable}'. – ZapFlow");
    }
}
