<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sku', 'barcode', 'name', 'product_type', 'category_path', 'category_id', 'brand',
        'cost_price', 'sale_price', 'stock_quantity', 'reserved_quantity',
        'min_stock', 'max_stock', 'unit', 'base_unit_code', 'conversion_rate',
        'attributes', 'related_sku', 'images', 'weight', 'is_active',
        'is_direct_sale', 'description', 'note_template', 'location', 'is_combo'
    ];

    protected $casts = [
        'attributes' => 'json',
        'images' => 'json',
        'is_active' => 'boolean',
        'is_direct_sale' => 'boolean',
        'is_combo' => 'boolean',
        'cost_price' => 'integer',
        'sale_price' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
