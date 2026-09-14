<?php

namespace App\Events;

use App\Models\TravelPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when the agency makes an outbound payment to a supplier / vendor.
 */
class TravelPaymentMadeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TravelPayment $payment;

    public function __construct(TravelPayment $payment)
    {
        $this->payment = $payment->loadMissing(['partyUser', 'bookingGroup.package']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('travel-payments.' . $this->payment->company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.made';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->payment->id,
            'amount'         => (float) $this->payment->amount,
            'currency'       => $this->payment->currency,
            'payment_date'   => optional($this->payment->payment_date)->toDateTimeString(),
            'method'         => $this->payment->payment_method,
            'paid_to'        => $this->payment->partyUser?->name,
            'booking_group'  => $this->payment->bookingGroup?->group_name,
            'reference'      => $this->payment->reference_no,
        ];
    }
}
