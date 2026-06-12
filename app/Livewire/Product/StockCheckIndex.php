<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\StockCheck;
use App\Models\StockCheckItem;
use App\Models\StockCheckLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasPermissions;
use Illuminate\Support\Str;

class StockCheckIndex extends Component
{
    use WithPagination, HasPermissions;

    public string $mode = 'list';
    public string $search = '';
    public string $dateFilter = 'month';
    public string $dateFrom = '';
    public string $dateTo = '';
    public array $statusFilter = ['draft', 'completed'];
    public string $creatorFilter = '';
    public array $selectedChecks = [];

    public ?int $stockCheckId = null;
    public string $code = '';
    public string $branch = 'hn';
    public string $status = 'draft';
    public string $note = '';
    public string $productSearch = '';
    public string $logSession = '';
    public string $lastLoggedSearch = '';
    public array $lines = [];

    protected function getModuleKey(): string
    {
        return 'products';
    }

    public function mount(): void
    {
        $this->logSession = (string) Str::uuid();
        $this->branch = auth()->user()?->work_branch ?: 'hn';
        if (!in_array($this->branch, ['hn', 'sg'], true)) {
            $this->branch = 'hn';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCreatorFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->stockCheckId = null;
        $this->code = '';
        $this->branch = auth()->user()?->work_branch ?: 'hn';
        if (!in_array($this->branch, ['hn', 'sg'], true)) {
            $this->branch = 'hn';
        }
        $this->status = 'draft';
        $this->note = '';
        $this->productSearch = '';
        $this->lastLoggedSearch = '';
        $this->logSession = (string) Str::uuid();
        $this->lines = [];
        $this->mode = 'edit';
        $this->persist('draft');
    }

    public function edit(int $id): void
    {
        $check = StockCheck::with('items')->findOrFail($id);
        $this->stockCheckId = $check->id;
        $this->code = $check->code;
        $this->branch = $check->branch ?: 'hn';
        $this->status = $check->status;
        $this->note = $check->note ?: '';
        $this->productSearch = '';
        $this->lastLoggedSearch = '';
        $this->logSession = (string) Str::uuid();

        // Refresh system_quantity từ stock hiện tại
        $productIds = $check->items->pluck('product_id')->filter()->all();
        $currentStocks = Product::whereIn('id', $productIds)->pluck('stock_quantity', 'id');

        $this->lines = $check->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'sku' => $item->sku,
            'name' => $item->product_name,
            'unit' => $item->unit ?: 'Cái',
            'system_quantity' => (int) ($currentStocks[$item->product_id] ?? $item->system_quantity),
            'actual_quantity' => (int) $item->actual_quantity,
            'difference' => (int) $item->difference,
            'difference_value' => (int) $item->difference_value,
        ])->values()->all();

        // Tính lại difference với system_quantity mới
        foreach (array_keys($this->lines) as $index) {
            $this->recalculateLine($index);
        }

        $this->mode = 'edit';

        if ($this->status === 'draft') {
            $this->persist('draft');
        }
    }

    public function cancelEdit(): void
    {
        if ($this->stockCheckId && empty($this->lines)) {
            StockCheckLog::where('stock_check_id', $this->stockCheckId)->delete();
            StockCheck::where('id', $this->stockCheckId)->delete();
        }
        $this->mode = 'list';
        $this->resetPage();
    }

    public function deleteCheck(int $id): void
    {
        if (!auth()->user()?->hasPermission('product.stock_check_delete')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xóa phiếu kiểm.', type: 'error');
            return;
        }

        StockCheckLog::where('stock_check_id', $id)->delete();
        StockCheck::where('id', $id)->delete();
        $this->resetPage();
        $this->dispatch('notify', message: 'Đã xóa phiếu kiểm.', type: 'success');
    }

    public function deleteSelected(): void
    {
        if (!auth()->user()?->hasPermission('product.stock_check_delete')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xóa phiếu kiểm.', type: 'error');
            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->selectedChecks)));
        if (empty($ids)) {
            $this->dispatch('notify', message: 'Vui lòng chọn phiếu kiểm cần xoá.', type: 'warning');
            return;
        }

        StockCheckLog::whereIn('stock_check_id', $ids)->delete();
        StockCheck::whereIn('id', $ids)->delete();
        $this->selectedChecks = [];
        $this->resetPage();
        $this->dispatch('notify', message: 'Đã xoá phiếu kiểm đã chọn.', type: 'success');
    }

    public function updatedProductSearch($value): void
    {
        $keyword = trim((string) $value);
        if ($keyword === '' || $keyword === $this->lastLoggedSearch) {
            return;
        }

        $this->lastLoggedSearch = $keyword;
        $this->logCheckAction('search', ['keyword' => $keyword]);
    }

    public function addProduct(int $productId): void
    {
        if (collect($this->lines)->contains('product_id', $productId)) {
            $product = Product::find($productId);
            $this->logCheckAction('duplicate_product', [
                'product' => $product,
                'keyword' => trim($this->productSearch) ?: null,
            ]);
            $this->productSearch = '';
            return;
        }

        $product = Product::findOrFail($productId);
        $systemQty = (int) $product->stock_quantity;

        $this->lines[] = [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name ?: $product->base_name,
            'unit' => $product->unit ?: $product->base_unit_code ?: 'Cái',
            'system_quantity' => $systemQty,
            'actual_quantity' => $systemQty,
            'difference' => 0,
            'difference_value' => 0,
        ];

        $this->logCheckAction('add_product', [
            'product' => $product,
            'keyword' => trim($this->productSearch) ?: null,
            'system_quantity' => $systemQty,
            'actual_quantity' => $systemQty,
            'difference' => 0,
        ]);

        $this->productSearch = '';
        $this->persist($this->status);
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->persist($this->status);
    }

    public function updatedLines($value, string $key): void
    {
        if (!str_ends_with($key, '.actual_quantity')) {
            return;
        }

        $index = (int) str($key)->before('.')->toString();
        $this->recalculateLine($index);
        $this->logLineAction('update_actual', $index);
        $this->persist($this->status);
    }

    private function recalculateLine(int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        $actual = max(0, (int) ($this->lines[$index]['actual_quantity'] ?? 0));
        $system = (int) ($this->lines[$index]['system_quantity'] ?? 0);
        $difference = $actual - $system;
        $this->lines[$index]['actual_quantity'] = $actual;
        $this->lines[$index]['difference'] = $difference;
        $this->lines[$index]['difference_value'] = abs($difference) * $this->productValue((int) $this->lines[$index]['product_id']);
    }

    private function productValue(int $productId): int
    {
        $product = Product::find($productId);
        return (int) ($product?->cost_price ?: $product?->sale_price ?: 0);
    }

    public function saveDraft(): void
    {
        $this->persist('draft');
        $this->logCheckAction('save_draft');
        $this->dispatch('notify', message: 'Đã lưu tạm phiếu kiểm kho.', type: 'success');
    }

    public function complete(): void
    {
        if (empty($this->lines)) {
            $this->dispatch('notify', message: 'Vui lòng thêm sản phẩm cần kiểm kho.', type: 'warning');
            return;
        }

        if ($this->status === 'completed') {
            $this->dispatch('notify', message: 'Phiếu này đã được cân bằng kho.', type: 'warning');
            return;
        }

        $check = $this->persist('completed');
        $this->logCheckAction('complete');

        foreach ($check->items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }

            if ((int) $item->difference !== 0) {
                $product->recordStockHistory(
                    'Adjustment',
                    (int) $item->difference,
                    $check->id,
                    $check->code,
                    'Cân bằng kho từ phiếu kiểm'
                );
            }

            $product->stock_quantity = (int) $item->actual_quantity;
            $product->save();
        }

        $check->update(['balanced_at' => now()]);
        $this->status = 'completed';
        $this->dispatch('notify', message: 'Đã hoàn thành và cân bằng kho.', type: 'success');
        $this->cancelEdit();
    }

    private function persist(string $status): StockCheck
    {
        foreach (array_keys($this->lines) as $index) {
            $this->recalculateLine((int) $index);
        }

        $totals = $this->totals();
        $check = StockCheck::updateOrCreate(
            ['id' => $this->stockCheckId],
            [
                'code' => $this->code ?: $this->nextCode(),
                'branch' => $this->branch,
                'user_id' => auth()->id(),
                'status' => $status,
                'note' => $this->note ?: null,
                'total_actual' => $totals['actual'],
                'total_difference' => $totals['difference'],
                'total_increase' => $totals['increase'],
                'total_decrease' => $totals['decrease'],
            ]
        );

        $check->items()->delete();
        foreach ($this->lines as $line) {
            StockCheckItem::create([
                'stock_check_id' => $check->id,
                'product_id' => $line['product_id'],
                'sku' => $line['sku'],
                'product_name' => $line['name'],
                'unit' => $line['unit'],
                'system_quantity' => (int) $line['system_quantity'],
                'actual_quantity' => (int) $line['actual_quantity'],
                'difference' => (int) $line['difference'],
                'difference_value' => (int) $line['difference_value'],
            ]);
        }

        $this->stockCheckId = $check->id;
        $this->code = $check->code;
        $this->status = $check->status;
        $this->attachSessionLogs($check);

        return $check->fresh('items');
    }

    private function attachSessionLogs(StockCheck $check): void
    {
        StockCheckLog::where('session_key', $this->logSession)
            ->whereNull('stock_check_id')
            ->update(['stock_check_id' => $check->id]);
    }

    private function logLineAction(string $action, int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        $line = $this->lines[$index];
        $this->logCheckAction($action, [
            'product_id' => $line['product_id'] ?? null,
            'sku' => $line['sku'] ?? null,
            'product_name' => $line['name'] ?? null,
            'system_quantity' => $line['system_quantity'] ?? null,
            'actual_quantity' => $line['actual_quantity'] ?? null,
            'difference' => $line['difference'] ?? null,
        ]);
    }

    private function logCheckAction(string $action, array $data = []): void
    {
        $product = $data['product'] ?? null;

        StockCheckLog::create([
            'stock_check_id' => $this->stockCheckId,
            'session_key' => $this->logSession,
            'user_id' => auth()->id(),
            'branch' => $this->branch,
            'action' => $action,
            'keyword' => $data['keyword'] ?? null,
            'product_id' => $data['product_id'] ?? $product?->id,
            'sku' => $data['sku'] ?? $product?->sku,
            'product_name' => $data['product_name'] ?? ($product ? ($product->name ?: $product->base_name) : null),
            'system_quantity' => isset($data['system_quantity']) ? (int) $data['system_quantity'] : null,
            'actual_quantity' => isset($data['actual_quantity']) ? (int) $data['actual_quantity'] : null,
            'difference' => isset($data['difference']) ? (int) $data['difference'] : null,
        ]);
    }

    public function getRecentLogsProperty()
    {
        return StockCheckLog::query()
            ->where(function ($query) {
                $query->when($this->stockCheckId, fn($q) => $q->where('stock_check_id', $this->stockCheckId))
                    ->orWhere('session_key', $this->logSession);
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    private function nextCode(): string
    {
        do {
            $code = 'KK' . now()->format('ymdHis');
        } while (StockCheck::where('code', $code)->exists());

        return $code;
    }

    public function totals(): array
    {
        $actual = collect($this->lines)->sum(fn($line) => (int) ($line['actual_quantity'] ?? 0));
        $difference = collect($this->lines)->sum(fn($line) => (int) ($line['difference'] ?? 0));
        $increase = collect($this->lines)->sum(fn($line) => max(0, (int) ($line['difference'] ?? 0)));
        $decrease = collect($this->lines)->sum(fn($line) => abs(min(0, (int) ($line['difference'] ?? 0))));

        return compact('actual', 'difference', 'increase', 'decrease');
    }

    public function getProductSuggestionsProperty()
    {
        $search = trim($this->productSearch);
        if (mb_strlen($search) < 1) {
            return collect();
        }

        return Product::query()
            ->where(function ($query) use ($search) {
                $keywords = array_filter(explode(' ', $search));
                foreach ($keywords as $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $pattern = '(^|[^0-9])' . $keyword . '([^0-9]|$)';
                        $q->whereRaw("sku REGEXP ?", [$pattern])
                            ->orWhereRaw("name REGEXP ?", [$pattern])
                            ->orWhereRaw("base_name REGEXP ?", [$pattern])
                            ->orWhereRaw("location REGEXP ?", [$pattern])
                            ->orWhereRaw("brand REGEXP ?", [$pattern]);
                    });
                }
            })
            ->orderByRaw("CASE
                WHEN sku = ? THEN 1
                WHEN sku LIKE ? THEN 2
                WHEN name = ? THEN 3
                WHEN base_name = ? THEN 4
                WHEN name LIKE ? THEN 5
                WHEN base_name LIKE ? THEN 6
                WHEN location = ? THEN 7
                WHEN brand = ? THEN 8
                ELSE 9
            END", [$search, $search . '%', $search, $search, $search . '%', $search . '%', $search, $search])
            ->limit(12)
            ->get();
    }

    private function checksQuery()
    {
        return StockCheck::with('user')
            ->when($this->search, fn($q) => $q->where('code', 'like', "%{$this->search}%"))
            ->when($this->dateFilter === 'month', fn($q) => $q->where('created_at', '>=', now()->startOfMonth()))
            ->when($this->dateFilter === 'custom' && $this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateFilter === 'custom' && $this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when(!empty($this->statusFilter), fn($q) => $q->whereIn('status', $this->statusFilter))
            ->when($this->creatorFilter, fn($q) => $q->where('user_id', $this->creatorFilter))
            ->latest();
    }

    public function render()
    {
        $checks = $this->checksQuery()->paginate(15);

        return view('livewire.product.stock-check-index', [
            'checks' => $checks,
            'creators' => User::query()
                ->whereIn('id', StockCheck::query()->select('user_id')->whereNotNull('user_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'totals' => $this->totals(),
        ])->layout('layouts.app');
    }
}
