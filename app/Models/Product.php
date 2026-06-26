<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'commission_amount',
        'commission_type',
        'commission_percent',
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
        'commission_amount' => 'integer',
        'commission_percent' => 'float',
    ];

    /**
     * Giá trị hoa hồng đã quy đổi ra TIỀN (VNĐ) cho 1 đơn vị sản phẩm.
     * - Loại 'percent': commission = giá bán chung × % (làm tròn).
     * - Loại 'amount' : commission = commission_amount.
     * Đây là giá trị dùng thống nhất ở POS / hóa đơn / báo cáo.
     */
    public function getCommissionValueAttribute(): int
    {
        if ($this->commission_type === 'percent') {
            return (int) round(((float) $this->sale_price) * ((float) $this->commission_percent) / 100);
        }
        return (int) $this->commission_amount;
    }

    protected static $autoCommissionCache = null;

    /** Có đang bật hoa hồng tự động theo dải giá hay không (cache theo request). */
    protected static function autoCommissionEnabled(): bool
    {
        if (self::$autoCommissionCache === null) {
            self::$autoCommissionCache = filter_var(
                SystemSetting::get('auto_commission_enabled', false),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        return self::$autoCommissionCache;
    }

    /**
     * Hoa hồng HIỆU LỰC (tiền) — nguồn duy nhất cho hiển thị & tính lợi nhuận.
     * = hoa hồng của SP (tiền hoặc % đã quy đổi).
     * Nếu SP chưa đặt (=0) VÀ đang bật hoa hồng tự động → lấy theo dải giá cấu hình chung.
     * Tắt hoa hồng tự động → 0 là 0 thật.
     */
    public function getEffectiveCommissionAttribute(): int
    {
        $commission = $this->commission_value;
        if ($commission <= 0 && self::autoCommissionEnabled()) {
            $commission = SystemSetting::commissionForPrice((int) $this->sale_price);
        }
        return (int) $commission;
    }

    /**
     * Lợi nhuận tạm tính = giá bán chung − hoa hồng hiệu lực − giá gốc.
     * Nếu CHƯA có giá gốc (cost_price <= 0): không đủ dữ liệu →
     * lợi nhuận tạm tính = hoa hồng hiệu lực.
     */
    public function getTempProfitAttribute(): int
    {
        $commission = $this->effective_commission;
        $cost = (int) $this->cost_price;
        if ($cost > 0) {
            return (int) $this->sale_price - $commission - $cost;
        }
        return $commission;
    }

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
        $allAttributes = self::whereNotNull('attributes')->pluck('attributes');
        $keys = [];
        foreach ($allAttributes as $attr) {
            if (is_array($attr)) {
                $keys = array_merge($keys, array_keys($attr));
            }
        }
        return array_values(array_unique(array_filter($keys)));
    }

    /**
     * Lấy danh sách các giá trị đã tồn tại cho một Key cụ thể
     */
    public static function getUniqueAttributeValues($key)
    {
        if (empty($key)) return [];
        
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
    }

    /**
     * Định dạng chuỗi vị trí (lưu dạng "A, B, C") để hiển thị: "A | B | C",
     * tối đa $max vị trí, nếu dư thì thêm "…".
     */
    public static function formatLocation(?string $location, int $max = 3): string
    {
        $parts = collect(explode(',', (string) $location))
            ->map(fn($p) => trim($p))
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return '';
        }

        $display = $parts->take($max)->implode(' | ');
        if ($parts->count() > $max) {
            $display .= ' …';
        }

        return $display;
    }

    /**
     * Vị trí dạng hiển thị: "A | B | C" (tối đa 3).
     */
    public function getLocationDisplayAttribute(): string
    {
        return static::formatLocation($this->location);
    }

    /**
     * Lấy danh sách các vị trí hàng hóa đã tồn tại (gợi ý khi nhập vị trí)
     */
    public static function getUniqueLocations()
    {
        return self::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->all();
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Đổi đường dẫn ảnh đã lưu (vd: "products/abc.jpg" trên disk public)
     * thành URL truy cập được (/storage/products/abc.jpg).
     * Ảnh đã là URL ngoài (http) hoặc đường dẫn tuyệt đối thì giữ nguyên.
     */
    public static function imageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * URL ảnh đầu tiên của sản phẩm (đã resolve sang /storage/...).
     */
    public function getImageUrlAttribute(): ?string
    {
        $images = is_array($this->images) ? $this->images : [];
        return self::imageUrl($images[0] ?? null);
    }

    /**
     * Ghi lại lịch sử thay đổi kho
     */
    public function recordStockHistory($type, $change, $referenceId = null, $referenceCode = null, $note = null, $quantityBefore = null)
    {
        $before = $quantityBefore !== null ? (int)$quantityBefore : (int)$this->stock_quantity;
        $after = $before + (int)$change;

        return StockHistory::create([
            'product_id' => $this->id,
            'type' => $type,
            'reference_id' => $referenceId,
            'reference_code' => $referenceCode,
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after' => $after,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);
    }
}
