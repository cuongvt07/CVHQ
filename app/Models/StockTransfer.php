<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'code', 'from_branch', 'to_branch', 'status', 'notes',
        'created_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
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
