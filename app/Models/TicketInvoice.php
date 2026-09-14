<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketInvoice extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'date',
        'booking_group_id',
        'customer_id',
        'airline_id',
        'passenger_id',
        'sector_id',
        'total_amount',
        'status',
        'ticket_count',
        'sale_type',
        'pnr',
        'refund_amount',
        'refund_reason',
        'refunded_at',
        'refunded_by',
        'added_by',
        'last_updated_by',
    ];

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(TicketRefund::class, 'ticket_invoice_id');
    }
}
