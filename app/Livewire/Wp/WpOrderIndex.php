<?php

namespace App\Livewire\Wp;

use App\Models\WpOrder;
use App\Services\WooCommerceService;
use App\Traits\HasPermissions;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class WpOrderIndex extends Component
{
    use WithPagination, HasPermissions;

    // Tab trạng thái xử lý: pending | unreachable | ordered | cannot_handle | all
    public string $statusFilter = 'pending';
    public string $search = '';
    public bool $syncing = false;

    // Modal "Không thể xử lý"
    public ?int $cannotHandleId = null;
    public string $cannotHandleReason = '';

    // Modal xem chi tiết đơn Mail
    public ?int $detailId = null;

    public function openDetail($id): void
    {
        $this->detailId = (int) $id;
        $this->dispatch('open-order-detail');
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    protected function getModuleKey(): string
    {
        return 'invoices';
    }

    #[On('wp-order-created')]
    public function refreshList(): void
    {
        // re-render để cập nhật trạng thái "Đã lên đơn".
    }

    public function mount(): void
    {
        $this->sync(false);
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
                $this->dispatch('notify', message: 'Lỗi đồng bộ đơn Mail: ' . $e->getMessage(), type: 'error');
            }
        }
        $this->syncing = false;
    }

    /** Trường hợp 2: gọi không được -> ghi 1 lần "không liên lạc được", đơn vẫn đang xử lý. */
    public function markUnreachable($id): void
    {
        $o = WpOrder::find($id);
        if (!$o || $o->local_status !== 'pending') {
            return;
        }
        $attempts = is_array($o->contact_attempts) ? $o->contact_attempts : [];
        $attempts[] = [
            'at'      => now()->toDateTimeString(),
            'by'      => auth()->id(),
            'by_name' => auth()->user()->name ?? 'NV',
        ];
        $o->update(['contact_attempts' => $attempts]);
        $this->dispatch('notify', message: 'Đã ghi nhận không liên lạc được (lần ' . count($attempts) . ').', type: 'success');
    }

    /** Mở modal nhập lý do "không thể xử lý". */
    public function requestCannotHandle($id): void
    {
        $this->cannotHandleId = (int) $id;
        $this->cannotHandleReason = '';
        $this->dispatch('open-cannot-handle');
    }

    /** Trường hợp 3: gọi được nhưng không mua / hẹn / lý do khác -> không thể xử lý. */
    public function confirmCannotHandle(): void
    {
        $reason = trim($this->cannotHandleReason);
        if ($reason === '') {
            $this->dispatch('notify', message: 'Vui lòng nhập lý do.', type: 'warning');
            return;
        }
        $o = WpOrder::find($this->cannotHandleId);
        if ($o && $o->local_status === 'pending') {
            $o->update([
                'local_status'         => 'cannot_handle',
                'cannot_handle_reason' => $reason,
                'cannot_handle_at'     => now(),
                'cannot_handle_by'     => auth()->id(),
            ]);
            $this->dispatch('notify', message: 'Đã đánh dấu không thể xử lý.', type: 'success');
        }
        $this->cannotHandleId = null;
        $this->cannotHandleReason = '';
        $this->dispatch('close-cannot-handle');
    }

    public function render()
    {
        $notCancelled = ['cancelled', 'refunded', 'failed', 'trash'];

        $orders = WpOrder::query()->with('localInvoice.user')
            ->when($this->statusFilter === 'pending', fn ($q) => $q
                ->where('local_status', 'pending')->whereNotIn('status', $notCancelled))
            ->when($this->statusFilter === 'unreachable', fn ($q) => $q
                ->where('local_status', 'pending')->whereNotIn('status', $notCancelled)
                ->whereNotNull('contact_attempts')->where('contact_attempts', '!=', '[]'))
            ->when($this->statusFilter === 'ordered', fn ($q) => $q->where('local_status', 'ordered'))
            ->when($this->statusFilter === 'cannot_handle', fn ($q) => $q->where('local_status', 'cannot_handle'))
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
            'openCount' => WpOrder::open()->count(),
            'detailOrder' => $this->detailId
                ? WpOrder::with('localInvoice.user', 'cannotHandleBy')->find($this->detailId)
                : null,
        ])->layout('layouts.app');
    }
}
