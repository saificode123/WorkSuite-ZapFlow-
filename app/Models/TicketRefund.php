<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRefund extends BaseModel
{
    protected $fillable = [
        'ticket_invoice_id',
        'amount',
        'reason',
        'refund_date',
        'refunded_by',
    ];

    public function ticketInvoice(): BelongsTo
    {
        return $this->belongsTo(TicketInvoice::class, 'ticket_invoice_id');
    }
}
