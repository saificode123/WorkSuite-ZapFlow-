<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * VoucherLockAuditTest
 *
 * Confirms that locking a Voucher (status: draft → locked) produces
 * exactly one audit_logs row with action='update'.
 *
 * This is the end-to-end proof that the audit trail is wired correctly —
 * the VoucherObserver is registered, audit_logs has the company_id column
 * the observer writes, and the row actually lands in the database.
 */
class VoucherLockAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // VoucherObserver calls user()?->id — returns null in tests (no auth),
        // which is fine: the observer uses ?-> so null user_id is stored.
        // permission_types must exist for CompanyObserver (triggered on any
        // Company create). We're not creating a company here, so we skip it.
    }

    /** @test */
    public function locking_a_draft_voucher_writes_an_audit_log_row(): void
    {
        // Insert a voucher directly via DB to bypass VoucherObserver::creating()
        // (which requires company/booking-group context). We only want to test
        // the updating() hook here.
        $now = now()->toDateTimeString();
        $voucherId = DB::table('vouchers')->insertGetId([
            'company_id'      => null,
            'booking_group_id'=> null,
            'type'            => 'full',
            'status'          => 'draft',
            'voucher_number'  => 'VCH-AUDIT-TEST-001',
            'version'         => 1,
            'charges_total'   => 0,
            'added_by'        => null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $auditCountBefore = AuditLog::count();

        // Fetch via Eloquent so model events fire.
        $voucher = Voucher::findOrFail($voucherId);

        // Change status from 'draft' to 'locked' — this is the trigger condition
        // inside VoucherObserver::updating().
        $voucher->status = 'locked';
        $voucher->save();

        // ── Assertions ───────────────────────────────────────────────────────

        $auditCountAfter = AuditLog::count();

        $this->assertEquals(
            $auditCountBefore + 1,
            $auditCountAfter,
            'Locking a voucher must produce exactly 1 new audit_logs row'
        );

        $log = AuditLog::latest()->first();

        $this->assertEquals('update', $log->action,
            'The audit action must be "update"');

        $this->assertEquals(Voucher::class, $log->entity_type,
            'entity_type must be the Voucher model class');

        $this->assertEquals($voucherId, $log->entity_id,
            'entity_id must match the locked voucher\'s ID');

        $this->assertNotNull($log->before,
            'before payload must be recorded (contains original status=draft)');

        $this->assertNotNull($log->after,
            'after payload must be recorded (contains dirty fields)');
    }

    /** @test */
    public function updating_a_non_status_field_still_writes_audit_log(): void
    {
        $now = now()->toDateTimeString();
        $voucherId = DB::table('vouchers')->insertGetId([
            'company_id'      => null,
            'booking_group_id'=> null,
            'type'            => 'full',
            'status'          => 'draft',
            'voucher_number'  => 'VCH-AUDIT-TEST-002',
            'version'         => 1,
            'charges_total'   => 0,
            'added_by'        => null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $voucher = Voucher::findOrFail($voucherId);
        $countBefore = AuditLog::count();

        // Change any field (version bump) — the observer's `isDirty()` block fires.
        $voucher->charges_total = 999.00;
        $voucher->save();

        $this->assertEquals($countBefore + 1, AuditLog::count(),
            'Any dirty update on a voucher must produce an audit_logs row');
    }
}
