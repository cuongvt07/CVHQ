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

    // ── Khóa check-in theo IP (mạng cửa hàng) ─────────────────────────────────
    /** IP client thực (ưu tiên X-Forwarded-For khi qua proxy/Cloudflare). */
    public static function clientIp(): ?string
    {
        $xff = request()->header('X-Forwarded-For');
        if ($xff) {
            $first = trim(explode(',', $xff)[0]);
            if ($first !== '') return $first;
        }
        return request()->ip();
    }

    public static function attendanceIpLock(): bool
    {
        return (bool) self::get('attendance_ip_lock', false);
    }

    public static function attendanceAllowedIps(): array
    {
        $ips = self::get('attendance_allowed_ips', []);
        if (!is_array($ips)) $ips = [];
        return array_values(array_filter(
            array_map(fn ($s) => trim((string) $s), $ips),
            fn ($s) => $s !== ''
        ));
    }

    /** IP có được phép check-in/out không. Không bật khóa -> luôn cho phép. */
    public static function ipAllowedForAttendance(?string $ip): bool
    {
        if (!self::attendanceIpLock()) return true;
        $ip = trim((string) $ip);
        if ($ip === '') return false;
        foreach (self::attendanceAllowedIps() as $rule) {
            // Tiền tố dải: "1.52." khớp mọi IP bắt đầu bằng chuỗi đó.
            if (str_ends_with($rule, '.')) {
                if (str_starts_with($ip, $rule)) return true;
            } elseif ($rule === $ip) {
                return true;
            }
        }
        return false;
    }

    // ── Khóa check-in theo THIẾT BỊ đã đăng ký (device token) ─────────────────
    public static function attendanceDeviceLock(): bool
    {
        return (bool) self::get('attendance_device_lock', false);
    }

    /** Danh sách thiết bị đã đăng ký: [{token, name, registered_at, by}]. */
    public static function attendanceDevices(): array
    {
        $d = self::get('attendance_devices', []);
        return is_array($d) ? $d : [];
    }

    /** Thiết bị (theo token trình duyệt gửi lên) có được phép chấm công không. */
    public static function deviceAllowedForAttendance(?string $token): bool
    {
        if (!self::attendanceDeviceLock()) return true;
        $token = trim((string) $token);
        if ($token === '') return false;
        foreach (self::attendanceDevices() as $dev) {
            if (hash_equals((string) ($dev['token'] ?? ''), $token)) return true;
        }
        return false;
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
