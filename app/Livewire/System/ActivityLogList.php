<?php

namespace App\Livewire\System;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockCheck;
use App\Models\StockTransfer;
use App\Support\LogPresenter;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;

class ActivityLogList extends Component
{
    use WithPagination, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'activity_logs';
    }

    public $search = '';
    public $user_id = '';
    public $action = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 25;
    public $sortDir = 'desc'; // sắp xếp theo thời gian: desc (mới nhất) | asc (cũ nhất)

    public $tab = 'all';

    public function toggleSort(): void
    {
        $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    protected function getDefaultVisibleColumns(): array
    {
        return ['time', 'user', 'action', 'object', 'details'];
    }

    protected $queryString = [
        'tab' => ['except' => 'all'],
        'user_id' => ['except' => ''],
        'action' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortDir' => ['except' => 'desc'],
    ];

    public function mount()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'user_id', 'action', 'date_from', 'date_to', 'tab'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'user_id', 'action', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function render()
    {
        if ($this->tab === 'stock' || $this->tab === 'import') {
            // Tồn kho = mọi biến động kho; Nhập hàng = chỉ phiếu nhập (type = Import)
            $logs = \App\Models\StockHistory::with(['user', 'product'])
                ->when($this->tab === 'import', fn($q) => $q->where('type', 'Import'))
                ->when($this->user_id, fn($q) => $q->where('user_id', $this->user_id))
                ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
                ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
                ->when($this->search, function($query) {
                    $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                          ->orWhereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                                                              ->orWhere('sku', 'like', '%' . $this->search . '%'))
                          ->orWhere('note', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', $this->sortDir === 'asc' ? 'asc' : 'desc')
                ->paginate($this->perPage);

            $this->decorateStock($logs, $this->tab === 'import');
        } else {
            // Activity Logs (All, Hóa đơn, Hàng hóa, Kiểm kho, Gửi hàng)
            $logs = ActivityLog::with('user')
                ->when($this->user_id, fn($query) => $query->where('user_id', $this->user_id))
                ->when($this->action, fn($query) => $query->where('action', $this->action))
                ->when($this->date_from, fn($query) => $query->whereDate('created_at', '>=', $this->date_from))
                ->when($this->date_to, fn($query) => $query->whereDate('created_at', '<=', $this->date_to))
                ->when($this->search, function($query) {
                    $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                          ->orWhere('model_type', 'like', '%' . $this->search . '%')
                          ->orWhere('model_id', 'like', '%' . $this->search . '%');
                })
                ->when($this->tab !== 'all', function($query) {
                    match($this->tab) {
                        'invoice' => $query->where('model_type', \App\Models\Invoice::class),
                        'product' => $query->whereIn('model_type', [\App\Models\Product::class, \App\Models\Category::class]),
                        'stock_check' => $query->where('model_type', \App\Models\StockCheck::class),
                        'transfer' => $query->where('model_type', \App\Models\StockTransfer::class),
                        default => null,
                    };
                })
                ->orderBy('created_at', $this->sortDir === 'asc' ? 'asc' : 'desc')
                ->paginate($this->perPage);

            $this->decorateActivity($logs);
        }

        return view('livewire.system.activity-log-list', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actions' => ['created', 'updated', 'deleted']
        ])->layout('layouts.app');
    }

    /**
     * Làm giàu các dòng StockHistory: SKU, tên đầy đủ, tồn từ→thành, URL chi tiết SP.
     */
    private function decorateStock($logs, bool $isImport): void
    {
        $typeMap = [
            'Import' => 'Nhập hàng', 'Sale' => 'Bán hàng', 'Cancel' => 'Hủy đơn',
            'Check' => 'Kiểm kho', 'Manual' => 'Chỉnh tay', 'Adjustment' => 'Điều chỉnh',
            'Transfer' => 'Chuyển hàng', 'Return' => 'Trả hàng', 'Initial' => 'Khởi tạo',
            'Delete' => 'Xóa hóa đơn', 'Purchase' => 'Nhập hàng',
        ];

        $logs->getCollection()->transform(function ($h) use ($typeMap, $isImport) {
            $change = (int) ($h->quantity_change ?? 0);
            $after  = (int) ($h->quantity_after ?? 0);
            $before = $h->quantity_before !== null ? (int) $h->quantity_before : ($after - $change);

            $h->action_label   = $typeMap[$h->type] ?? $h->type;
            $h->badge          = $change >= 0 ? 'success' : 'error';
            $h->entity_primary = $h->product->sku ?? '—';
            $h->entity_secondary = $h->product->name ?? 'Sản phẩm đã xóa';
            $h->detail_url     = $h->product_id ? route('products', ['open' => $h->product_id]) : null;

            if ($isImport) {
                $h->custom_details = 'Nhập ' . ($change > 0 ? '+' : '') . $change . ' • Tồn hiện tại: ' . number_format($after);
            } else {
                $h->custom_details = 'Tồn: ' . number_format($before) . ' → ' . number_format($after)
                    . ' (' . ($change > 0 ? '+' : '') . $change . ')';
            }
            if ($h->note) {
                $h->custom_details .= ' • ' . $h->note;
            }

            return $h;
        });
    }

    /**
     * Làm giàu các dòng ActivityLog: mã/SKU/tên đối tượng (kể cả đã xóa qua snapshot),
     * nhãn hành động cụ thể, URL chi tiết.
     */
    private function decorateActivity($logs): void
    {
        $col = $logs->getCollection();
        $idsOf = fn(array $classes) => $col->whereIn('model_type', $classes)
            ->pluck('model_id')->filter()->unique()->all();

        $invoices  = Invoice::whereIn('id', $idsOf([Invoice::class]))->pluck('invoice_code', 'id');
        $products  = Product::withTrashed()->whereIn('id', $idsOf([Product::class]))
            ->get(['id', 'sku', 'base_name', 'name'])->keyBy('id');
        $categories = \App\Models\Category::whereIn('id', $idsOf([\App\Models\Category::class]))->pluck('name', 'id');
        $checks    = StockCheck::whereIn('id', $idsOf([StockCheck::class]))->pluck('code', 'id');
        $transfers = StockTransfer::whereIn('id', $idsOf([StockTransfer::class]))->pluck('code', 'id');

        $col->transform(function ($log) use ($invoices, $products, $categories, $checks, $transfers) {
            $base = class_basename($log->model_type);
            $snap = $log->changes['snapshot'] ?? [];
            $log->entity_secondary = null;

            if ($base === 'Invoice') {
                $log->entity_primary = $invoices[$log->model_id] ?? ($snap['invoice_code'] ?? ('#' . $log->model_id));
            } elseif ($base === 'Product') {
                $p = $products[$log->model_id] ?? null;
                $log->entity_primary   = $p->sku ?? ($snap['sku'] ?? ('#' . $log->model_id));
                $log->entity_secondary = $p ? ($p->base_name ?: $p->name) : ($snap['base_name'] ?? $snap['name'] ?? null);
            } elseif ($base === 'Category') {
                $log->entity_primary = $categories[$log->model_id] ?? ($snap['name'] ?? ('#' . $log->model_id));
            } elseif ($base === 'StockCheck') {
                $log->entity_primary = $checks[$log->model_id] ?? ($snap['code'] ?? ('#' . $log->model_id));
            } elseif ($base === 'StockTransfer') {
                $log->entity_primary = $transfers[$log->model_id] ?? ($snap['code'] ?? ('#' . $log->model_id));
            } else {
                $log->entity_primary = '#' . $log->model_id;
            }

            $log->action_label = LogPresenter::actionLabel($log->model_type, $log->action, $log->changes);
            $log->badge        = LogPresenter::actionType($log->action);
            $log->detail_url   = LogPresenter::detailUrl($log->model_type, $log->model_id);

            return $log;
        });
    }
}
