<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'address', 'phone', 'manager', 'color', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Bảng màu cho nhãn chi nhánh (class Tailwind có sẵn trong build). */
    public const COLORS = [
        'rose'    => ['text' => 'text-rose-700 border-rose-300',       'dot' => 'bg-rose-500'],
        'emerald' => ['text' => 'text-emerald-700 border-emerald-300', 'dot' => 'bg-emerald-500'],
        'blue'    => ['text' => 'text-blue-700 border-blue-300',       'dot' => 'bg-blue-500'],
        'amber'   => ['text' => 'text-amber-700 border-amber-300',     'dot' => 'bg-amber-500'],
        'violet'  => ['text' => 'text-violet-700 border-violet-300',   'dot' => 'bg-violet-500'],
        'slate'   => ['text' => 'text-slate-600 border-slate-200',     'dot' => 'bg-slate-400'],
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('branches_active');
        static::saved($flush);
        static::deleted($flush);
    }

    /** Các chi nhánh đang bật, sắp theo sort_order (cache theo request/5 phút). */
    public static function active()
    {
        return Cache::remember('branches_active', 300, function () {
            return static::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        });
    }

    /** [code => name] dùng cho dropdown. */
    public static function options(): array
    {
        return static::active()->pluck('name', 'code')->all();
    }

    /**
     * Map cho thanh chuyển chi nhánh: bao gồm 'all' + các chi nhánh active.
     * [code => ['label','color','dot']]
     */
    public static function uiMap(bool $withAll = true): array
    {
        $map = [];
        if ($withAll) {
            $map['all'] = ['label' => 'Tất cả', 'color' => self::COLORS['slate']['text'], 'dot' => self::COLORS['slate']['dot']];
        }
        foreach (static::active() as $b) {
            $c = self::COLORS[$b->color] ?? self::COLORS['slate'];
            $map[$b->code] = ['label' => $b->name, 'color' => $c['text'], 'dot' => $c['dot']];
        }
        return $map;
    }

    /** Tên hiển thị theo code (fallback viết hoa code). */
    public static function nameOf(?string $code): string
    {
        if (!$code) {
            return '';
        }
        $b = static::active()->firstWhere('code', $code);
        return $b ? $b->name : strtoupper($code);
    }

    /** Tìm theo code (kể cả đang tắt). */
    public static function byCode(?string $code): ?self
    {
        if (!$code) {
            return null;
        }
        return static::where('code', $code)->first();
    }
}
