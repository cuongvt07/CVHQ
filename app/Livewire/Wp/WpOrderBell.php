<?php

namespace App\Livewire\Wp;

use App\Models\WpOrder;
use App\Services\WooCommerceService;
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

    /** Poll định kỳ: đồng bộ đơn WP + kêu chuông nếu có đơn mới. */
    public function tick(): void
    {
        $wasEmpty = WpOrder::count() === 0;
        $new = 0;
        try {
            $res = app(WooCommerceService::class)->sync(20);
            $new = (int) ($res['new'] ?? 0);
        } catch (\Throwable $e) {
            // im lặng nếu WP không phản hồi
        }
        $this->refreshCounts();

        // Không kêu cho lần đồng bộ đầu tiên (DB đang rỗng).
        if (!$wasEmpty && $new > 0) {
            $this->dispatch('wp-new-order', count: $new);
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
