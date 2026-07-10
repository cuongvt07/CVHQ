<?php

namespace App\Livewire\Wp;

use App\Models\WpOrder;
use App\Services\WooCommerceService;
use App\Traits\HasPermissions;
use Livewire\Component;
use Livewire\WithPagination;

class WpOrderIndex extends Component
{
    use WithPagination, HasPermissions;

    public string $statusFilter = 'pending'; // pending | all | processing | completed | cancelled
    public string $search = '';
    public bool $syncing = false;

    protected function getModuleKey(): string
    {
        return 'invoices';
    }

    public function mount(): void
    {
        // Đồng bộ nhẹ khi mở trang.
        $this->sync(false);
        // Đánh dấu đã xem (tắt chấm chuông) khi vào trang.
        WpOrder::where('seen', false)->update(['seen' => true]);
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sync(bool $notify = true): void
    {
        $this->syncing = true;
        try {
            $result = app(WooCommerceService::class)->sync(30);
            if ($notify) {
                $msg = $result['new'] > 0
                    ? "Đã đồng bộ, có {$result['new']} đơn mới."
                    : 'Đã đồng bộ, không có đơn mới.';
                $this->dispatch('notify', message: $msg, type: 'success');
            }
        } catch (\Throwable $e) {
            if ($notify) {
                $this->dispatch('notify', message: 'Lỗi đồng bộ WooCommerce: ' . $e->getMessage(), type: 'error');
            }
        }
        $this->syncing = false;
    }

    /** Đánh dấu đã xử lý thủ công (không tạo đơn nội bộ). */
    public function markHandled($id): void
    {
        $o = WpOrder::find($id);
        if ($o && !$o->local_invoice_id) {
            $o->update(['handled_at' => now(), 'handled_by' => auth()->id(), 'status' => $o->status ?: 'processing']);
            // đánh dấu bằng local_invoice_id = 0 để ẩn khỏi "chưa xử lý"? -> dùng handled_at thay thế.
        }
        $this->dispatch('notify', message: 'Đã đánh dấu xử lý.', type: 'success');
    }

    public function render()
    {
        $orders = WpOrder::query()
            ->when($this->statusFilter === 'pending', fn ($q) => $q->pending()->whereNull('handled_at'))
            ->when(in_array($this->statusFilter, ['processing', 'completed', 'cancelled'], true),
                fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn ($w) => $w->where('customer_name', 'like', $s)
                    ->orWhere('customer_phone', 'like', $s)
                    ->orWhere('number', 'like', $s));
            })
            ->orderByDesc('wp_created_at')
            ->paginate(20);

        return view('livewire.wp.wp-order-index', [
            'orders' => $orders,
            'pendingCount' => WpOrder::pending()->whereNull('handled_at')->count(),
        ])->layout('layouts.app');
    }
}
