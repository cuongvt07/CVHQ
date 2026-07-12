<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WpOrder extends Model
{
    protected $fillable = [
        'wp_id', 'number', 'status', 'local_status', 'customer_name', 'customer_phone', 'customer_email',
        'address', 'payment_method', 'payment_title', 'total', 'shipping_total', 'discount_total',
        'items', 'customer_note', 'wp_created_at', 'local_invoice_id', 'handled_at', 'handled_by',
        'contact_attempts', 'cannot_handle_reason', 'cannot_handle_at', 'cannot_handle_by',
        'seen', 'synced_at',
    ];

    protected $casts = [
        'items' => 'array',
        'contact_attempts' => 'array',
        'total' => 'integer',
        'shipping_total' => 'integer',
        'discount_total' => 'integer',
        'seen' => 'boolean',
        'wp_created_at' => 'datetime',
        'handled_at' => 'datetime',
        'cannot_handle_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    /** Hóa đơn nội bộ đã lập từ đơn Mail này (nếu đã lên đơn). */
    public function localInvoice(): BelongsTo
    {
        // withTrashed: đơn Mail vẫn tra ra hóa đơn gốc kể cả khi HĐ bị xóa/hủy.
        return $this->belongsTo(Invoice::class, 'local_invoice_id')->withTrashed();
    }

    public function cannotHandleBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cannot_handle_by')->withTrashed();
    }

    /**
     * Đơn còn phải xử lý (chưa lên đơn, chưa đánh dấu không thể xử lý, WP chưa hủy).
     * = số hiện badge đỏ trên icon Mail.
     */
    public function scopeOpen($query)
    {
        return $query->where('local_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'refunded', 'failed', 'trash']);
    }

    /** Giữ tương thích code cũ. */
    public function scopePending($query)
    {
        return $query->whereNull('local_invoice_id')
            ->whereNotIn('status', ['cancelled', 'refunded', 'failed', 'trash']);
    }

    public function isHandled(): bool
    {
        return $this->local_invoice_id !== null;
    }

    /** Nhãn trạng thái xử lý nội bộ (dùng cho tab / badge). */
    public function localStatusLabel(): string
    {
        return match ($this->local_status) {
            'ordered'       => 'Đã lên đơn',
            'cannot_handle' => 'Không thể xử lý',
            default         => !empty($this->contact_attempts) ? 'Không liên lạc được' : 'Chưa xử lý',
        };
    }
}
