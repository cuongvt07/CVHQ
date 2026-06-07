<?php

namespace App\Livewire\System;

use App\Models\ActivityLog;
use App\Models\User;
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

    public $tab = 'all';

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
        if ($this->tab === 'stock') {
            // Tồn kho: StockHistory
            $logs = \App\Models\StockHistory::with(['user', 'product'])
                ->when($this->user_id, fn($q) => $q->where('user_id', $this->user_id))
                ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
                ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
                ->when($this->search, function($query) {
                    $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                          ->orWhereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                          ->orWhere('note', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);

            // Transform for unified blade
            $logs->getCollection()->transform(function ($item) {
                $item->action = 'updated';
                $item->model_name = 'Tồn kho: ' . ($item->product->name ?? 'SP');
                $item->model_id = $item->product_id;
                
                $typeMap = ['Import' => 'Nhập hàng', 'Sale' => 'Bán hàng', 'Cancel' => 'Hủy đơn', 'Check' => 'Kiểm kho', 'Manual' => 'Chỉnh tay'];
                $item->custom_details = ($typeMap[$item->type] ?? $item->type) . ' (' . ($item->quantity_change > 0 ? '+' : '') . $item->quantity_change . ')';
                if ($item->note) $item->custom_details .= ' - ' . $item->note;
                
                return $item;
            });
        } elseif ($this->tab === 'import') {
            // Nhập hàng: Cảnh báo hết hàng, sắp hết hàng
            $logs = \App\Models\Product::where('is_active', true)
                ->where('stock_quantity', '<=', 5)
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                ->orderBy('stock_quantity', 'asc')
                ->paginate($this->perPage);

            // Transform for unified blade
            $logs->getCollection()->transform(function ($item) {
                $item->user = (object)['name' => 'Hệ thống'];
                $item->action = $item->stock_quantity <= 0 ? 'error' : 'warning';
                $item->model_name = $item->stock_quantity <= 0 ? 'Hết hàng' : 'Sắp hết hàng';
                $item->model_id = $item->id;
                $item->custom_details = $item->name . ' - Còn lại: ' . $item->stock_quantity;
                return $item;
            });
        } else {
            // Activity Logs (All, Hóa đơn, Hàng hóa, Kiểm kho)
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
                        default => null,
                    };
                })
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.system.activity-log-list', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actions' => ['created', 'updated', 'deleted']
        ])->layout('layouts.app');
    }
}
