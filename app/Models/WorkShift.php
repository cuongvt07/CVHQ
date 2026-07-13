<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WorkShift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    /** Chuyển "HH:MM[:SS]" -> số phút trong ngày. */
    private static function toMinutes(?string $time): int
    {
        if (!$time) return 0;
        $p = explode(':', $time);
        return ((int) ($p[0] ?? 0)) * 60 + ((int) ($p[1] ?? 0));
    }

    /** Thời lượng ca (phút). Qua đêm (end <= start) cộng thêm 24h. */
    public function getDurationMinutesAttribute(): int
    {
        $d = self::toMinutes($this->end_time) - self::toMinutes($this->start_time);
        return $d <= 0 ? $d + 1440 : $d;
    }

    public function getStartLabelAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndLabelAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    /**
     * Nhận diện ca theo giờ check-in: ca có giờ BẮT ĐẦU gần thời điểm check-in nhất
     * (không phân công trước; check trước/sau mốc đều tính ca đó). Null nếu chưa có ca nào.
     */
    public static function detectForCheckIn(Carbon $at): ?self
    {
        $shifts = self::where('is_active', true)->get();
        if ($shifts->isEmpty()) {
            return null;
        }
        $mod = $at->hour * 60 + $at->minute;
        return $shifts->sortBy(function ($s) use ($mod) {
            $start = self::toMinutes($s->start_time);
            $diff = abs($mod - $start);
            return min($diff, 1440 - $diff); // tính cả vòng qua nửa đêm
        })->first();
    }
}
