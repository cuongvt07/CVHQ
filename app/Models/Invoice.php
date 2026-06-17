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
        'invoice_code', 'branch', 'customer_id', 'user_id', 'seller_name', 'sales_channel',
        'total_amount', 'discount_amount', 'extra_fee', 'extra_fee_name', 'final_amount', 'total_commission',
        'paid_amount', 'cash_amount', 'card_amount', 'wallet_amount',
        'transfer_amount', 'status', 'delivery_status', 'created_at',
        'cancel_reason', 'cancelled_at', 'cancelled_by',
        'shared_commission_amount', 'shared_to_user_id'
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
        'extra_fee' => 'integer',
        'final_amount' => 'integer',
        'paid_amount' => 'integer',
        'total_commission' => 'integer',
        'shared_commission_amount' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Xóa hóa đơn được coi như trả hàng: hoàn lại tồn kho + ghi thẻ kho (loại "Delete" = Xóa đơn).
        // Chỉ hoàn khi đơn đang hiệu lực (chưa Hủy/Trả) để tránh hoàn kho trùng.
        static::deleting(function (Invoice $invoice) {
            if ($invoice->isForceDeleting()) {
                return;
            }
            if (in_array($invoice->status, ['Cancelled', 'Returned'], true)) {
                return;
            }

            foreach ($invoice->items as $item) {
                $qty = (int) $item->quantity;
                if ($qty === 0) {
                    continue;
                }

                $product = $item->product()->withTrashed()->first();
                if (!$product && $item->sku) {
                    $product = Product::withTrashed()->where('sku', $item->sku)->first();
                }
                if (!$product) {
                    continue;
                }

                $before = (int) $product->stock_quantity;
                $product->increment('stock_quantity', $qty);
                $product->recordStockHistory(
                    'Delete',
                    $qty,
                    $invoice->id,
                    $invoice->invoice_code,
                    'Xóa hóa đơn (hoàn kho)',
                    $before
                );
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function sharedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_to_user_id');
    }

    /**
     * Derive readable payment method label from amount columns
     */
    public function getPaymentMethodLabel(): string
    {
        $methods = [];
        if ($this->cash_amount > 0) $methods[] = 'Tiền mặt';
        if ($this->transfer_amount > 0) $methods[] = 'Chuyển khoản';
        if ($this->card_amount > 0) $methods[] = 'Thẻ';
        if ($this->wallet_amount > 0) $methods[] = 'Ví';
        return implode(', ', $methods) ?: 'Tiền mặt';
    }

    /**
     * Derive payment method key (cash/transfer/card/wallet)
     */
    public function getPaymentMethodKey(): string
    {
        if ($this->transfer_amount > 0) return 'transfer';
        if ($this->card_amount > 0) return 'card';
        if ($this->wallet_amount > 0) return 'wallet';
        return 'cash';
    }

    public function cancel(string $reason, int $userId): void
    {
        // Đã Hủy hoặc đã Trả hàng => tồn kho đã được hoàn rồi, KHÔNG hoàn lại lần nữa.
        if (in_array($this->status, ['Cancelled', 'Returned'], true)) {
            return;
        }

        \DB::transaction(function () use ($reason, $userId) {
            // Restore stock for each item (withTrashed to handle soft-deleted products)
            foreach ($this->items as $item) {
                $product = $item->product()->withTrashed()->first();
                if ($product) {
                    $product->recordStockHistory(
                        'Cancel', 
                        $item->quantity, 
                        $this->id, 
                        $this->invoice_code, 
                        'Hủy hóa đơn'
                    );
                    $product->increment('stock_quantity', $item->quantity);
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
