<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends BaseModel
{
    use HasCompany;

    public const TYPES = [
        'accommodation' => 'Accommodation',
        'transport'     => 'Transport',
        'full'          => 'Full Voucher',
    ];

    public const STATUSES = [
        'draft'   => 'Draft',
        'locked'  => 'Locked',
        'issued'  => 'Issued',
    ];

    protected $fillable = [
        'company_id',
        'booking_group_id',
        'voucher_number',
        'type',
        'date',
        'status',
        'charges_total',
        'pdf_path',
        'qr_code',
        'qr_payload',
        'version',
        'locked_at',
        'locked_by',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(VoucherCharge::class, 'voucher_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'issued']);
    }

    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }

    public function scopeLocked($q)
    {
        return $q->where('status', 'locked');
    }

    public function scopeIssued($q)
    {
        return $q->where('status', 'issued');
    }
}
