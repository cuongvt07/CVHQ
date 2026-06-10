<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id', 'from_product_id', 'to_product_id',
        'from_sku', 'to_sku', 'product_name', 'image',
        'from_stock', 'to_stock', 'send_quantity',
        'actual_quantity', 'adjust_reason',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function fromProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'from_product_id');
    }

    public function toProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'to_product_id');
    }

    public function getEffectiveQuantityAttribute(): int
    {
        return $this->actual_quantity ?? $this->send_quantity;
    }
}
