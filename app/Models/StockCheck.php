<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCheck extends Model
{
    protected $fillable = [
        'code',
        'branch',
        'user_id',
        'status',
        'note',
        'total_actual',
        'total_difference',
        'total_increase',
        'total_decrease',
        'balanced_at',
    ];

    protected $casts = [
        'balanced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCheckItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(StockCheckLog::class);
    }
}
