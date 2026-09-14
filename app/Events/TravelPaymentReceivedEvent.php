<?php

namespace App\Events;

use App\Models\TravelPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast whenever a customer-side travel payment is received.
 *
 * Subscribed by the operations dashboard — keeps finance/ops in sync
 * without polling the DataTable every few seconds.
 */
class TravelPaymentReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TravelPayment $payment;

    public function __construct(TravelPayment $payment)
    {
        $this->payment = $payment->loadMissing(['partyUser', 'bookingGroup.package']);
    }

    public function broadcastOn(): array
    {
        // Per-company private channel — only users in the same tenant see it.
        return [
            new PrivateChannel('travel-payments.' . $this->payment->company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->payment->id,
            'amount'         => (float) $this->payment->amount,
            'currency'       => $this->payment->currency,
            'payment_date'   => optional($this->payment->payment_date)->toDateTimeString(),
            'method'         => $this->payment->payment_method,
            'received_from'  => $this->payment->partyUser?->name,
            'booking_group'  => $this->payment->bookingGroup?->group_name,
            'reference'      => $this->payment->reference_no,
        ];
    }
}
