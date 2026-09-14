<?php

namespace App\Events;

use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a voucher is scanned/redeemed at a hotel or service point.
 * Triggers dashboard refresh for the issuing agent.
 */
class VoucherRedeemedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Voucher $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher->loadMissing(['bookingGroup.package', 'hotel', 'passenger']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vouchers.' . $this->voucher->company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'voucher.redeemed';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->voucher->id,
            'voucher_number' => $this->voucher->voucher_number,
            'booking_group'  => $this->voucher->bookingGroup?->group_name,
            'hotel'          => $this->voucher->hotel?->name,
            'passenger'      => $this->voucher->passenger?->full_name,
            'redeemed_at'    => optional($this->voucher->redeemed_at)->toDateTimeString(),
            'amount'         => (float) $this->voucher->total_amount,
        ];
    }
}
