<?php

namespace App\Livewire\Wp;

use App\Models\WpOrder;
use Livewire\Component;

class WpOrderBell extends Component
{
    public int $count = 0;   // số đơn WP chưa xử lý
    public int $unseen = 0;  // số đơn mới chưa xem (chấm đỏ)

    public function mount(): void
    {
        $this->refreshCounts();
    }

    protected function refreshCounts(): void
    {
        $this->count = WpOrder::pending()->whereNull('handled_at')->count();
        $this->unseen = WpOrder::where('seen', false)->count();
    }

    /**
     * Poll định kỳ (NHẸ): chỉ ĐẾM DB, không gọi API WooCommerce ngoài mỗi 30s
     * (trước đây gọi HTTP ngoài trên MỌI trang -> khựng định kỳ). Đơn mới vẫn về
     * real-time qua webhook; kêu chuông khi số đếm tăng.
     */
    public function tick(): void
    {
        $old = $this->count;
        $this->refreshCounts();
        if ($this->count > $old) {
            $this->dispatch('wp-new-order', count: $this->count - $old);
        }
    }

    /** Đánh dấu đã đọc (tắt chấm đỏ) khi mở tab thông báo. */
    public function markSeen(): void
    {
        WpOrder::where('seen', false)->update(['seen' => true]);
        $this->unseen = 0;
    }

    public function render()
    {
        return view('livewire.wp.wp-order-bell', [
            'recent' => WpOrder::pending()->whereNull('handled_at')
                ->orderByDesc('wp_created_at')->limit(8)->get(),
        ]);
    }
}
