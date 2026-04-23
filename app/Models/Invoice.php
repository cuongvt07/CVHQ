<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_code', 'branch', 'customer_id', 'seller_name', 'sales_channel',
        'total_amount', 'discount_amount', 'extra_fee', 'final_amount',
        'paid_amount', 'cash_amount', 'card_amount', 'wallet_amount',
        'transfer_amount', 'status', 'delivery_status', 'created_at'
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
        'extra_fee' => 'integer',
        'final_amount' => 'integer',
        'paid_amount' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(InvoiceShipping::class);
    }
}
