<?php

namespace App\Observers;

use App\Models\Passenger;
use App\Models\AuditLog;
use App\Models\VisaLog;

class PassengerObserver
{
    public function created(Passenger $passenger): void
    {
        VisaLog::create([
            'company_id' => $passenger->bookingGroup?->company_id,
            'passenger_id' => $passenger->id,
            'from_status' => null,
            'to_status' => 'draft',
            'changed_by' => user()?->id,
            'remarks' => 'Passenger created',
        ]);
    }

    public function updating(Passenger $passenger): void
    {
        if ($passenger->isDirty('visa_pipeline_status')) {
            VisaLog::create([
                'company_id' => $passenger->bookingGroup?->company_id,
                'passenger_id' => $passenger->id,
                'from_status' => $passenger->getOriginal('visa_pipeline_status'),
                'to_status' => $passenger->visa_pipeline_status,
                'changed_by' => user()?->id,
                'remarks' => request()?->rejection_reason ?? request()?->remarks ?? 'Status updated',
            ]);

            $passenger->visa_status_updated_by = user()?->id;
            $passenger->visa_status_updated_at = now();
        }

        if ($passenger->isDirty()) {
            AuditLog::create([
                'company_id' => $passenger->bookingGroup?->company_id,
                'user_id' => user()?->id,
                'action' => 'update',
                'entity_type' => Passenger::class,
                'entity_id' => $passenger->id,
                'before' => json_encode($passenger->getOriginal()),
                'after' => json_encode($passenger->getDirty()),
            ]);
        }
    }

    public function deleted(Passenger $passenger): void
    {
        AuditLog::create([
            'company_id' => $passenger->bookingGroup?->company_id,
            'user_id' => user()?->id,
            'action' => 'delete',
            'entity_type' => Passenger::class,
            'entity_id' => $passenger->id,
            'before' => json_encode($passenger->toArray()),
            'after' => null,
        ]);
    }
}
