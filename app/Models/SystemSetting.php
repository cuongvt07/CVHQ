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

    // ── Cấu hình đi muộn / phạt lương ─────────────────────────────────────────
    /** Giờ vào chuẩn (HH:MM). Sau mốc này coi là đi muộn. */
    public static function lateStartTime(): string
    {
        $t = self::get('attendance_start_time', '08:30');
        return (is_string($t) && preg_match('/^\d{1,2}:\d{2}$/', $t)) ? $t : '08:30';
    }

    /** Các bậc phạt đi muộn, đã lọc hợp lệ + sắp xếp tăng dần theo số phút. */
    public static function latePenaltyTiers(): array
    {
        $tiers = self::get('attendance_penalties', []);
        if (!is_array($tiers)) $tiers = [];
        $out = [];
        foreach ($tiers as $t) {
            $m = (int) ($t['minutes'] ?? 0);
            $a = (int) ($t['amount'] ?? 0);
            if ($m > 0 && $a > 0) $out[] = ['minutes' => $m, 'amount' => $a];
        }
        usort($out, fn ($x, $y) => $x['minutes'] <=> $y['minutes']);
        return $out;
    }

    /** Số phút đi muộn của 1 lần check-in (0 nếu đúng/sớm giờ). */
    public static function lateMinutesFor(\Illuminate\Support\Carbon $checkIn): int
    {
        [$h, $m] = array_pad(explode(':', self::lateStartTime()), 2, '0');
        $startMod = ((int) $h) * 60 + (int) $m;
        $inMod    = $checkIn->hour * 60 + $checkIn->minute;
        return max(0, $inMod - $startMod);
    }

    /** Tiền phạt tương ứng số phút đi muộn (lấy bậc cao nhất thoả). */
    public static function latePenaltyFor(int $lateMinutes): int
    {
        if ($lateMinutes <= 0) return 0;
        $penalty = 0;
        foreach (self::latePenaltyTiers() as $t) {
            if ($lateMinutes >= $t['minutes']) $penalty = $t['amount'];
        }
        return $penalty;
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
