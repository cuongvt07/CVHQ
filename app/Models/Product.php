<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\Loggable;

class Product extends Model
{
    use SoftDeletes, Loggable;
    protected $fillable = [
        'sku',
        'barcode',
        'base_name',
        'name',
        'product_type',
        'category_id',
        'category_path',
        'brand',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'reserved_quantity',
        'min_stock',
        'max_stock',
        'unit',
        'base_unit_code',
        'conversion_rate',
        'attributes',
        'images',
        'is_active',
        'is_direct_sale',
        'related_sku',
        'weight',
        'description',
        'note_template',
        'location',
        'is_combo',
        'commission_amount'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($product) {
            $product->name = $product->generateFullName();
        });
    }

    public function generateFullName()
    {
        $fullName = $this->base_name ?? $this->name ?? '';
        $customAttributes = $this->getAttribute('attributes') ?? [];
        
        if (is_array($customAttributes)) {
            foreach ($customAttributes as $key => $value) {
                if (!empty($value)) {
                    // Split multiple values if they exist (comma separated)
                    $values = explode(',', (string)$value);
                    foreach ($values as $val) {
                        $val = trim($val);
                        if (!empty($val) && mb_stripos($fullName, $val) === false) {
                            $fullName .= ' ' . $val;
                        }
                    }
                }
            }
        }
        
        return trim($fullName);
    }

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

    /**
     * Lấy danh sách tất cả các Key thuộc tính đã tồn tại
     */
    public static function getUniqueAttributeKeys()
    {
        return \Illuminate\Support\Facades\Cache::remember('unique_product_attribute_keys', 3600, function () {
            $allAttributes = self::whereNotNull('attributes')->pluck('attributes');
            $keys = [];
            foreach ($allAttributes as $attr) {
                if (is_array($attr)) {
                    $keys = array_merge($keys, array_keys($attr));
                }
            }
            return array_values(array_unique(array_filter($keys)));
        });
    }

    /**
     * Lấy danh sách các giá trị đã tồn tại cho một Key cụ thể
     */
    public static function getUniqueAttributeValues($key)
    {
        if (empty($key)) return [];
        
        return \Illuminate\Support\Facades\Cache::remember('unique_product_attribute_values_' . md5($key), 3600, function () use ($key) {
            $allAttributes = self::whereNotNull('attributes')->pluck('attributes');
            $values = [];
            foreach ($allAttributes as $attr) {
                if (is_array($attr) && isset($attr[$key])) {
                    // Tách các giá trị nếu chúng được lưu dạng chuỗi phẩy "Đỏ, Xanh"
                    $parts = explode(',', $attr[$key]);
                    foreach ($parts as $part) {
                        $values[] = trim($part);
                    }
                }
            }
            return array_values(array_unique(array_filter($values)));
        });
    }
}
