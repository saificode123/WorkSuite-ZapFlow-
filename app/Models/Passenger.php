<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Passenger extends BaseModel
{
    protected $table = 'passengers';

    protected $guarded = ['id'];

    protected $fillable = [
        'booking_group_id',
        'passport_no',
        'first_name',
        'family_name',
        'birth_date',
        'gender',
        'mofa_status',
        'visa_pipeline_status',
        'visa_mofa_ref',
        'visa_rejection_reason',
        'visa_status_updated_by',
        'visa_status_updated_at',
        'relation_id',
        'mahram_passenger_id',
        'room_allocation_id',
        'added_by',
        'last_updated_by',
    ];

    protected $dates = ['birth_date', 'visa_status_updated_at'];

    /**
     * Sensitive columns — masked in list views; reveal is audit-logged.
     */
    public static array $sensitiveColumns = ['passport_no', 'birth_date'];

    /**
     * Visa pipeline status flow.
     */
    public const VISA_STATUSES = [
        'draft'            => 'Draft',
        'sent_to_embassy'  => 'Sent to Embassy',
        'mofa_received'    => 'MoFA Received',
        'issued'           => 'Issued',
        'rejected'         => 'Rejected',
    ];

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }

    /** Mahram (guardian) passenger link */
    public function mahram(): BelongsTo
    {
        return $this->belongsTo(self::class, 'mahram_passenger_id');
    }

    /** Dependents who list this passenger as mahram */
    public function dependents(): HasMany
    {
        return $this->hasMany(self::class, 'mahram_passenger_id');
    }

    public function passportDeliveries(): HasMany
    {
        return $this->hasMany(PassportDelivery::class, 'passenger_id');
    }

    public function repeatFees(): HasMany
    {
        return $this->hasMany(RepeatFee::class, 'passenger_id');
    }

    public function mutamerTransfers(): HasMany
    {
        return $this->hasMany(MutamerTransfer::class, 'passenger_id');
    }

    public function roomAllocation(): HasOne
    {
        return $this->hasOne(RoomAllocation::class, 'passenger_id')
            ->whereIn('status', ['reserved', 'checked_in'])
            ->latest();
    }

    public function allRoomAllocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class, 'passenger_id');
    }

    public function insuranceSales(): HasMany
    {
        return $this->hasMany(InsuranceSale::class, 'passenger_id');
    }

    public function ticketInvoices(): HasMany
    {
        return $this->hasMany(TicketInvoice::class, 'passenger_id');
    }

    public function visaStatusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visa_status_updated_by');
    }

    /** Full name helper */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->family_name);
    }

    /** Human-readable visa status label */
    public function getVisaStatusLabelAttribute(): string
    {
        return self::VISA_STATUSES[$this->visa_pipeline_status] ?? ucfirst($this->visa_pipeline_status ?? 'draft');
    }

    /** Masked passport number for list views */
    public function getMaskedPassportNoAttribute(): string
    {
        $no = $this->passport_no ?? '';
        if (strlen($no) <= 4) {
            return str_repeat('*', strlen($no));
        }
        return substr($no, 0, 2) . str_repeat('*', strlen($no) - 4) . substr($no, -2);
    }
}

