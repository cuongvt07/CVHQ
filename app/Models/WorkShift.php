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
     * Nhận diện ca theo giờ check-in (không phân công trước):
     *   1) Nếu thời điểm check-in NẰM TRONG khung giờ 1 ca -> tính ca đó (đang trong ca).
     *   2) Nếu không trong ca nào (đến sớm/trễ/giữa 2 ca) -> ca có mốc BẮT ĐẦU gần nhất.
     * Null nếu chưa cấu hình ca nào.
     */
    public static function detectForCheckIn(Carbon $at): ?self
    {
        $shifts = self::where('is_active', true)->get();
        if ($shifts->isEmpty()) {
            return null;
        }
        $mod = $at->hour * 60 + $at->minute;

        // 1) Ca có khung giờ chứa thời điểm check-in.
        $within = $shifts->filter(function ($s) use ($mod) {
            $start = self::toMinutes($s->start_time);
            $end   = self::toMinutes($s->end_time);
            return $end > $start
                ? ($mod >= $start && $mod <= $end)
                : ($mod >= $start || $mod <= $end); // ca qua đêm
        })->sortBy(fn ($s) => abs($mod - self::toMinutes($s->start_time)));
        if ($within->isNotEmpty()) {
            return $within->first();
        }

        // 2) Không trong ca nào -> mốc bắt đầu gần nhất (tính cả vòng qua nửa đêm).
        return $shifts->sortBy(function ($s) use ($mod) {
            $start = self::toMinutes($s->start_time);
            $diff = abs($mod - $start);
            return min($diff, 1440 - $diff);
        })->first();
    }
}
