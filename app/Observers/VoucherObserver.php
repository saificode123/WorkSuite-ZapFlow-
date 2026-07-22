<?php

namespace App\Observers;

use App\Models\Voucher;
use App\Models\AuditLog;

class VoucherObserver
{
    public function creating(Voucher $voucher): void
    {
        if (!$voucher->voucher_number) {
            $voucher->voucher_number = 'VCH-' . now()->format('Ymd') . '-' . str_pad(Voucher::max('id') + 1, 4, '0', STR_PAD_LEFT);
        }
        $voucher->version = 1;
    }

    public function updating(Voucher $voucher): void
    {
        if ($voucher->isDirty('status') && $voucher->getOriginal('status') === 'draft' && in_array($voucher->status, ['locked', 'issued'])) {
            $voucher->locked_at = now();
            $voucher->locked_by = user()?->id;

            if (!$voucher->qr_payload) {
                $payload = json_encode([
                    'v' => $voucher->voucher_number,
                    'b' => $voucher->booking_group_id,
                    's' => $voucher->status,
                    't' => $voucher->type,
                    'h' => hash_hmac('sha256', $voucher->id . '|' . $voucher->version, config('app.key')),
                ]);
                $voucher->qr_payload = $payload;
            }
        }

        if ($voucher->isDirty() && $voucher->exists) {
            AuditLog::create([
                'company_id' => $voucher->company_id,
                'user_id' => user()?->id,
                'action' => 'update',
                'entity_type' => Voucher::class,
                'entity_id' => $voucher->id,
                'before' => json_encode($voucher->getOriginal()),
                'after' => json_encode($voucher->getDirty()),
            ]);
        }
    }

    public function deleting(Voucher $voucher): void
    {
        if ($voucher->isLocked()) {
            throw new \RuntimeException('Cannot delete a locked/issued voucher.');
        }
        $voucher->charges()->delete();
    }
}
