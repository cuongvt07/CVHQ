<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Tồn của 1 thùng PHỤ của sản phẩm (thùng gốc = tồn tổng - tổng thùng phụ, xem Product::boxBreakdown()).
class ProductBoxStock extends Model
{
    protected $fillable = ['product_id', 'box_code', 'quantity'];

    protected $casts = ['quantity' => 'integer'];
}
