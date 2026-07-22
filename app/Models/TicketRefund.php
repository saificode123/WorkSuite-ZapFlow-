<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRefund extends BaseModel
{
    protected $guarded = ['id'];

    public function ticketInvoice(): BelongsTo
    {
        return $this->belongsTo(TicketInvoice::class, 'ticket_invoice_id');
    }
}
