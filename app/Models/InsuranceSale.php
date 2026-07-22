<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceSale extends BaseModel
{
    use HasCompany;

    protected $table = 'insurance_sales';
    protected $guarded = ['id'];

    protected $fillable = [
        'company_id',
        'passenger_id',
        'policy_id',
        'booking_group_id',
        'amount',
        'currency_code',
        'account_id',
        'certificate_number',
        'status',
        'added_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id');
    }

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
