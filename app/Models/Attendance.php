<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'work_shift_id', 'shift_name', 'shift_minutes',
        'check_in_at', 'check_out_at', 'worked_minutes', 'work_date',
    ];

    protected $casts = [
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
        'work_date'    => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    /** Số giờ công đã tính (đã capped). */
    public function getWorkedHoursAttribute(): float
    {
        return round(($this->worked_minutes ?? 0) / 60, 2);
    }

    public function isOpen(): bool
    {
        return $this->check_out_at === null;
    }
}
