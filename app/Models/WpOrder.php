<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpOrder extends Model
{
    protected $fillable = [
        'wp_id', 'number', 'status', 'customer_name', 'customer_phone', 'customer_email',
        'address', 'payment_method', 'payment_title', 'total', 'shipping_total', 'discount_total',
        'items', 'customer_note', 'wp_created_at', 'local_invoice_id', 'handled_at', 'handled_by',
        'seen', 'synced_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'integer',
        'shipping_total' => 'integer',
        'discount_total' => 'integer',
        'seen' => 'boolean',
        'wp_created_at' => 'datetime',
        'handled_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    /** Đơn chưa xử lý (chưa tạo đơn nội bộ, chưa hủy). */
    public function scopePending($query)
    {
        return $query->whereNull('local_invoice_id')
            ->whereNotIn('status', ['cancelled', 'refunded', 'failed', 'trash']);
    }

    public function isHandled(): bool
    {
        return $this->local_invoice_id !== null;
    }
}
