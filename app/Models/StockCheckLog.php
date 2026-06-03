<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCheckLog extends Model
{
    protected $fillable = [
        'stock_check_id',
        'session_key',
        'user_id',
        'branch',
        'action',
        'keyword',
        'product_id',
        'sku',
        'product_name',
        'system_quantity',
        'actual_quantity',
        'difference',
    ];

    public function stockCheck(): BelongsTo
    {
        return $this->belongsTo(StockCheck::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
