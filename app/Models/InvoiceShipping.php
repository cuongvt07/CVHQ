<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceShipping extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'shipping_partner', 'shipping_fee', 'receiver_name',
        'receiver_phone', 'receiver_address', 'delivery_time', 'delivery_note'
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'delivery_time' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
