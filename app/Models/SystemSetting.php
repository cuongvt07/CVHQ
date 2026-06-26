<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * URL logo hệ thống đã resolve sang /storage/... (null nếu chưa cấu hình).
     */
    public static function logoUrl(): ?string
    {
        $path = self::get('app_logo');
        if (empty($path)) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Tra mức hoa hồng (TIỀN) theo giá bán dựa trên bảng dải giá "commission_ranges"
     * trong cấu hình chung. Mốc trên (max) tính BAO GỒM. Trả 0 nếu không khớp dải nào.
     * Dùng chung cho ProductIndex / CommissionSettings / Product::tempProfit.
     */
    /** Cache dải giá trong 1 request để tránh N+1 khi tính cho nhiều sản phẩm. */
    protected static $commissionRangesCache = null;

    public static function commissionForPrice(int $price): int
    {
        if (self::$commissionRangesCache === null) {
            $ranges = self::get('commission_ranges', []);
            self::$commissionRangesCache = is_array($ranges) ? $ranges : [];
        }
        foreach (self::$commissionRangesCache as $range) {
            $min = (int) ($range['min'] ?? 0);
            $max = (int) ($range['max'] ?? 0);
            $amount = (int) ($range['amount'] ?? 0);
            if ($price >= $min && ($max <= 0 || $price <= $max)) {
                return $amount;
            }
        }
        return 0;
    }

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        $value = $setting->value;
        
        // Try to decode JSON
        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    public static function set($key, $value, $description = null)
    {
        // Reset cache dải giá nếu thay đổi cấu hình hoa hồng.
        if ($key === 'commission_ranges') {
            self::$commissionRangesCache = null;
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string)$value, 'description' => $description]
        );
    }
}
