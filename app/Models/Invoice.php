<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\Loggable;

class Invoice extends Model
{
    use SoftDeletes, Loggable;
    protected $fillable = [
        'invoice_code', 'branch', 'customer_id', 'user_id', 'seller_name', 'sales_channel', 'sales_channel_id',
        'total_amount', 'discount_amount', 'extra_fee', 'extra_fee_name', 'final_amount', 'total_commission',
        'paid_amount', 'cash_amount', 'card_amount', 'wallet_amount',
        'transfer_amount', 'status', 'delivery_status', 'created_at',
        'cancel_reason', 'cancelled_at', 'cancelled_by'
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
        'extra_fee' => 'integer',
        'final_amount' => 'integer',
        'paid_amount' => 'integer',
        'total_commission' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salesChannel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class, 'sales_channel_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function cancel(string $reason, int $userId): void
    {
        if ($this->status === 'Cancelled') {
            return;
        }

        \DB::transaction(function () use ($reason, $userId) {
            // Restore stock for each item
            foreach ($this->items as $item) {
                if ($item->product) {
                    $item->product->recordStockHistory(
                        'Cancel', 
                        $item->quantity, 
                        $this->id, 
                        $this->invoice_code, 
                        'Hủy hóa đơn'
                    );
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $this->update([
                'status' => 'Cancelled',
                'cancel_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
            ]);
        });
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
