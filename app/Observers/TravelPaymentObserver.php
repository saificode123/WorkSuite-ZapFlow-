<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\TravelPayment;

/**
 * TravelPaymentObserver
 *
 * Writes an audit_logs row whenever a TravelPayment record is created.
 * This ensures every financial movement (agent receipt or vendor payment)
 * tied to a booking group is permanently auditable.
 */
class TravelPaymentObserver
{
    public function created(TravelPayment $payment): void
    {
        AuditLog::create([
            'company_id'  => $payment->company_id,
            'user_id'     => user()?->id,
            'action'      => 'payment_' . $payment->payment_direction, // 'payment_receive' or 'payment_make'
            'entity_type' => TravelPayment::class,
            'entity_id'   => $payment->id,
            'before'      => null,
            'after'       => [
                'direction'        => $payment->payment_direction,
                'amount'           => $payment->amount,
                'currency'         => $payment->currency_code,
                'booking_group_id' => $payment->booking_group_id,
                'reference_no'     => $payment->reference_no,
            ],
            'ip_address'  => request()?->ip(),
        ]);
    }
}
