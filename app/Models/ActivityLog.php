<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user(): BelongsTo
    {
        // Lấy cả nhân viên đã xóa mềm để nhật ký vẫn hiển thị đúng tên người thao tác.
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the descriptive name of the model being logged.
     */
    public function getModelNameAttribute()
    {
        $parts = explode('\\', $this->model_type);
        return end($parts);
    }
}
