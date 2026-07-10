<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'code', 'from_branch', 'to_branch', 'status', 'notes', 'tracking_code',
        'created_by', 'confirmed_by', 'confirmed_at',
        'shipped_at', 'shipped_by', 'received_at', 'received_by',
        'sender_confirmed_at', 'sender_confirmed_by',
    ];

    protected $casts = [
        'confirmed_at'        => 'datetime',
        'shipped_at'          => 'datetime',
        'received_at'         => 'datetime',
        'sender_confirmed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by')->withTrashed();
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by')->withTrashed();
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withTrashed();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Nháp',
            'shipping'  => 'Đang vận chuyển',
            'received'  => 'Đã nhận · chờ xác nhận',
            'completed' => 'Đã hoàn thành',
            'confirmed' => 'Đã hoàn thành', // legacy
            default     => $this->status,
        };
    }

    public function getFromBranchLabelAttribute(): string
    {
        return match ($this->from_branch) {
            'hn' => 'Hà Nội',
            'sg' => 'Sài Gòn',
            default => strtoupper($this->from_branch),
        };
    }

    public function getToBranchLabelAttribute(): string
    {
        return match ($this->to_branch) {
            'hn' => 'Hà Nội',
            'sg' => 'Sài Gòn',
            default => strtoupper($this->to_branch),
        };
    }
}
