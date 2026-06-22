<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'reference_id',
        'reference_code',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'note',
        'user_id'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        // Lấy cả nhân viên đã xóa mềm để thẻ kho vẫn hiển thị đúng tên người thao tác.
        return $this->belongsTo(User::class)->withTrashed();
    }
}
