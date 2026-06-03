<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCheckItem extends Model
{
    protected $fillable = [
        'stock_check_id',
        'product_id',
        'sku',
        'product_name',
        'unit',
        'system_quantity',
        'actual_quantity',
        'difference',
        'difference_value',
    ];

    public function stockCheck(): BelongsTo
    {
        return $this->belongsTo(StockCheck::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
