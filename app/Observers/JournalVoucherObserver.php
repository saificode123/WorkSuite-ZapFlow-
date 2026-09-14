<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\JournalVoucher;

/**
 * JournalVoucherObserver
 *
 * Writes an audit_logs row on every create and update of a JournalVoucher.
 * This satisfies the requirement that financial entries are fully auditable
 * without scattering AuditLog::create() calls across multiple controllers.
 */
class JournalVoucherObserver
{
    public function created(JournalVoucher $jv): void
    {
        AuditLog::create([
            'company_id'  => $jv->company_id,
            'user_id'     => user()?->id,
            'action'      => 'create',
            'entity_type' => JournalVoucher::class,
            'entity_id'   => $jv->id,
            'before'      => null,
            'after'       => $jv->toArray(),
            'ip_address'  => request()?->ip(),
        ]);
    }

    public function updated(JournalVoucher $jv): void
    {
        if (!$jv->isDirty()) {
            return;
        }

        AuditLog::create([
            'company_id'  => $jv->company_id,
            'user_id'     => user()?->id,
            'action'      => 'update',
            'entity_type' => JournalVoucher::class,
            'entity_id'   => $jv->id,
            'before'      => $jv->getOriginal(),
            'after'       => $jv->getDirty(),
            'ip_address'  => request()?->ip(),
        ]);
    }
}
