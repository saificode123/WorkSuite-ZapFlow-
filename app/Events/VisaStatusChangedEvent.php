<?php

namespace App\Events;

use App\Models\Passenger;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast on every visa pipeline stage transition.
 * Drives the visa kanban real-time view and notifies the assigned agent.
 */
class VisaStatusChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Passenger $passenger;
    public ?string $fromStatus;

    public function __construct(Passenger $passenger, ?string $fromStatus)
    {
        $this->passenger  = $passenger->loadMissing(['bookingGroup.package', 'visaStatusUpdatedBy']);
        $this->fromStatus = $fromStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('visa-pipeline.' . $this->passenger->company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'visa.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'passenger_id'      => $this->passenger->id,
            'passenger_name'    => $this->passenger->full_name ?? trim(($this->passenger->first_name ?? '') . ' ' . ($this->passenger->last_name ?? '')),
            'booking_group'     => $this->passenger->bookingGroup?->group_name,
            'package'           => $this->passenger->bookingGroup?->package?->name,
            'from_status'       => $this->fromStatus,
            'to_status'         => $this->passenger->visa_pipeline_status,
            'mofa_ref'          => $this->passenger->visa_mofa_ref,
            'rejection_reason'  => $this->passenger->visa_rejection_reason,
            'updated_by'        => $this->passenger->visaStatusUpdatedBy?->name,
            'updated_at'        => optional($this->passenger->visa_status_updated_at)->toDateTimeString(),
        ];
    }
}
