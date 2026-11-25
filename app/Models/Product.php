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

        // Danh sách filter (danh mục/thương hiệu/vị trí) được cache ở ProductIndex
        // -> xóa cache khi dữ liệu sản phẩm thay đổi để filter luôn cập nhật.
        $forget = fn () => \Cache::forget('product_filter_lists');
        static::saved($forget);
        static::deleted($forget);
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
        $parts = collect(static::splitLocation($location));

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
     * Tách chuỗi vị trí thành từng thùng: "D50, D51 | D52" -> ['D50','D51','D52'].
     * Khử trùng không phân biệt hoa/thường; thùng ĐẦU TIÊN là "thùng gốc".
     */
    public static function splitLocation(?string $location): array
    {
        $seen = [];
        foreach (preg_split('/[,;|
]+/', (string) $location) as $token) {
            $token = trim($token);
            if ($token !== '' && !isset($seen[mb_strtolower($token)])) {
                $seen[mb_strtolower($token)] = $token;
            }
        }
        return array_values($seen);
    }

    /** Chuẩn hóa để lưu: "d50 | D51,D52" -> "d50, D51, D52". */
    public static function normalizeLocation(?string $location): string
    {
        return implode(', ', static::splitLocation($location));
    }

    /**
     * Vị trí dạng HTML: thùng gốc (đầu tiên) IN ĐẬM, các thùng còn lại thường, ngăn cách " | ".
     */
    public static function formatLocationHtml(?string $location, int $max = 3): \Illuminate\Support\HtmlString
    {
        $parts = static::splitLocation($location);
        if (!$parts) {
            return new \Illuminate\Support\HtmlString('');
        }
        $out = [];
        foreach (array_slice($parts, 0, $max) as $i => $p) {
            $out[] = $i === 0 ? '<b class="font-black">' . e($p) . '</b>' : e($p);
        }
        $html = implode(' | ', $out);
        if (count($parts) > $max) {
            $html .= ' …';
        }
        return new \Illuminate\Support\HtmlString($html);
    }

    public function getLocationHtmlAttribute(): \Illuminate\Support\HtmlString
    {
        return static::formatLocationHtml($this->location);
    }

    /**
     * Danh mục thùng: mọi mã thùng xuất hiện trong vị trí SP + thùng đã có ghi chú.
     * Trả về [['code','note','count'], ...] sắp xếp tự nhiên (D2 < D10).
     */
    public static function boxCatalog(): array
    {
        $counts = [];
        self::whereNotNull('location')->where('location', '!=', '')->pluck('location')->each(function ($loc) use (&$counts) {
            foreach (static::splitLocation($loc) as $token) {
                $k = mb_strtolower($token);
                $counts[$k] = [
                    'code' => $counts[$k]['code'] ?? $token,
                    'count' => ($counts[$k]['count'] ?? 0) + 1,
                ];
            }
        });

        $notes = \App\Models\BoxNote::pluck('note', 'code')->mapWithKeys(fn($n, $c) => [mb_strtolower($c) => [$c, $n]]);
        foreach ($notes as $k => [$code, $note]) {
            $counts[$k] = ['code' => $counts[$k]['code'] ?? $code, 'count' => $counts[$k]['count'] ?? 0];
        }

        $list = [];
        foreach ($counts as $k => $row) {
            $list[] = ['code' => $row['code'], 'count' => $row['count'], 'note' => $notes[$k][1] ?? ''];
        }
        usort($list, fn($a, $b) => strnatcasecmp($a['code'], $b['code']));
        return $list;
    }

    /**
     * Gợi ý thùng chứa NHIỀU mã trong kết quả tìm kiếm hiện tại (để nhân viên tới thùng đó nhặt hàng).
     * $query: truy vấn SP đã áp bộ lọc/tìm kiếm (chưa phân trang).
     */
    public static function boxSuggestions($query, int $limit = 4, int $minCount = 2): array
    {
        $tally = [];
        foreach ((clone $query)->reorder()->whereNotNull('location')->where('location', '!=', '')->limit(3000)->pluck('location') as $loc) {
            foreach (static::splitLocation($loc) as $token) {
                $k = mb_strtolower($token);
                $tally[$k] = ['code' => $tally[$k]['code'] ?? $token, 'count' => ($tally[$k]['count'] ?? 0) + 1];
            }
        }
        $tally = array_filter($tally, fn($r) => $r['count'] >= $minCount);
        if (!$tally) {
            return [];
        }
        uasort($tally, fn($a, $b) => $b['count'] <=> $a['count']);
        $tally = array_slice($tally, 0, $limit, true);

        $notes = \App\Models\BoxNote::whereIn('code', array_column($tally, 'code'))->pluck('note', 'code');
        return array_values(array_map(fn($r) => $r + ['note' => (string) ($notes[$r['code']] ?? '')], $tally));
    }

    /**
     * Lấy danh sách các vị trí hàng hóa đã tồn tại (gợi ý khi nhập vị trí)
     */
    public static function getUniqueLocations()
    {
        $raw = self::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location');

        // Gợi ý gồm CẢ chuỗi gốc (VD "Kệ trưng bày|D01") LẪN từng mã tách ra
        // (D01, D02...) để gõ tiền tố/hậu tố ra hết. Khử trùng không phân biệt hoa thường.
        $set = [];
        foreach ($raw as $loc) {
            $loc = trim((string) $loc);
            if ($loc === '') continue;
            $set[mb_strtolower($loc)] = $loc; // giữ nguyên cụm gốc
            foreach (preg_split('/[,;|\/\r\n\t]+/', $loc) as $token) {
                $token = trim($token);
                if ($token !== '') {
                    $set[mb_strtolower($token)] = $token; // + từng mã lẻ
                }
            }
        }
        $values = array_values($set);
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);
        return $values;
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
