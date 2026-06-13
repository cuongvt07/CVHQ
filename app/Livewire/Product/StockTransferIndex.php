<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Traits\HasPermissions;
use Livewire\Component;
use Livewire\WithPagination;

class StockTransferIndex extends Component
{
    use HasPermissions, WithPagination;

    protected function getModuleKey(): string
    {
        return 'products';
    }

    // ── List state ───────────────────────────────────────────────────────────
    public string $statusFilter = 'all';
    public string $branchFilter = 'all';

    // ── Edit mode ────────────────────────────────────────────────────────────
    public string $mode = 'list';
    public ?int $editingId = null;
    public string $fromBranch = 'hn';
    public string $toBranch = 'sg';
    public string $transferCode = '';
    public string $status = 'draft';
    public string $notes = '';
    public array $lines = [];

    // ── Product search ───────────────────────────────────────────────────────
    public string $productSearch = '';
    public array $searchResults = [];

    public function mount(): void
    {
        $userBranch = auth()->user()?->work_branch ?? 'hn';
        $this->fromBranch = in_array($userBranch, ['hn', 'sg']) ? $userBranch : 'hn';
        $this->toBranch   = $this->fromBranch === 'hn' ? 'sg' : 'hn';
    }

    // ── Create / Edit ────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->editingId    = null;
        $this->lines        = [];
        $this->notes        = '';
        $this->status       = 'draft';
        $this->transferCode = '';
        $this->productSearch = '';
        $this->searchResults = [];
        $this->mode = 'edit';
    }

    public function editTransfer(int $id): void
    {
        $transfer = StockTransfer::with('items')->find($id);
        if (!$transfer) return;

        $this->editingId    = $id;
        $this->fromBranch   = $transfer->from_branch;
        $this->toBranch     = $transfer->to_branch;
        $this->transferCode = $transfer->code;
        $this->status       = $transfer->status;
        $this->notes        = $transfer->notes ?? '';

        $this->lines = $transfer->items->map(fn($item) => [
            'from_product_id' => $item->from_product_id,
            'to_product_id'   => $item->to_product_id,
            'from_sku'        => $item->from_sku,
            'to_sku'          => $item->to_sku,
            'product_name'    => $item->product_name,
            'image'           => $item->image,
            'from_stock'      => $item->from_stock,
            'to_stock'        => $item->to_stock,
            'send_quantity'   => $item->send_quantity,
            'actual_quantity' => $item->actual_quantity,
            'adjust_reason'   => $item->adjust_reason ?? '',
        ])->toArray();

        $this->productSearch = '';
        $this->searchResults = [];
        $this->mode = 'edit';
    }

    public function cancelEdit(): void
    {
        $this->mode      = 'list';
        $this->editingId = null;
        $this->lines     = [];
        $this->resetPage();
    }

    // ── Product management ───────────────────────────────────────────────────

    public function addProduct(int $productId): void
    {
        foreach ($this->lines as $line) {
            if ((int)$line['from_product_id'] === $productId) {
                $this->dispatch('notify', message: 'Sản phẩm đã có trong danh sách.', type: 'warning');
                return;
            }
        }

        $product = Product::find($productId);
        if (!$product) return;

        $related = $product->related_sku
            ? Product::where('sku', $product->related_sku)->first()
            : null;

        $this->lines[] = [
            'from_product_id' => $product->id,
            'to_product_id'   => $related?->id,
            'from_sku'        => $product->sku,
            'to_sku'          => $related?->sku ?? $product->related_sku,
            'product_name'    => $product->base_name ?: $product->name,
            'image'           => $product->images[0] ?? null,
            'from_stock'      => $product->stock_quantity,
            'to_stock'        => $related?->stock_quantity ?? 0,
            'send_quantity'   => 1,
            'actual_quantity' => null,
            'adjust_reason'   => '',
        ];

        $this->productSearch = '';
        $this->searchResults = [];
        $this->autoSave();
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
        $this->autoSave();
    }

    public function updatedLines(): void
    {
        $this->autoSave();
    }

    public function updatedProductSearch(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $existingIds = array_column($this->lines, 'from_product_id');

        $this->searchResults = Product::where(function ($q) {
                $q->where('sku', 'like', "%{$this->productSearch}%")
                  ->orWhere('base_name', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->whereNotIn('id', $existingIds)
            ->orderBy('sku')
            ->take(12)
            ->get(['id', 'sku', 'base_name', 'name', 'stock_quantity', 'images', 'related_sku'])
            ->map(fn($p) => [
                'id'          => $p->id,
                'sku'         => $p->sku,
                'name'        => $p->base_name ?: $p->name,
                'stock'       => $p->stock_quantity,
                'related_sku' => $p->related_sku,
                'image'       => $p->images[0] ?? null,
            ])
            ->toArray();
    }

    // ── Persist ──────────────────────────────────────────────────────────────

    public function autoSave(): void
    {
        if (empty($this->lines) && !$this->editingId) return;
        $this->persistToDb($this->status);
    }

    public function saveDraft(): void
    {
        $this->persistToDb('draft');
        $this->dispatch('notify', message: 'Đã lưu tạm phiếu chuyển hàng.', type: 'success');
    }

    private function persistToDb(string $status): void
    {
        $data = [
            'from_branch' => $this->fromBranch,
            'to_branch'   => $this->toBranch,
            'created_by'  => auth()->id(),
            'status'      => $status,
            'notes'       => $this->notes,
        ];

        if ($this->editingId) {
            $transfer = StockTransfer::find($this->editingId);
            if (!$transfer) return;
            $transfer->update($data);
        } else {
            $code     = 'CK-' . strtoupper($this->fromBranch) . strtoupper($this->toBranch) . '-' . date('ymdHis');
            $transfer = StockTransfer::create(array_merge($data, ['code' => $code]));
            $this->editingId    = $transfer->id;
            $this->transferCode = $transfer->code;
        }

        $transfer->items()->delete();
        foreach ($this->lines as $line) {
            $transfer->items()->create([
                'from_product_id' => $line['from_product_id'],
                'to_product_id'   => $line['to_product_id'] ?? null,
                'from_sku'        => $line['from_sku'],
                'to_sku'          => $line['to_sku'] ?? null,
                'product_name'    => $line['product_name'],
                'image'           => $line['image'] ?? null,
                'from_stock'      => $line['from_stock'],
                'to_stock'        => $line['to_stock'],
                'send_quantity'   => max(0, (int)($line['send_quantity'] ?? 1)),
                'actual_quantity' => isset($line['actual_quantity']) && $line['actual_quantity'] !== null
                    ? (int)$line['actual_quantity']
                    : null,
                'adjust_reason'   => $line['adjust_reason'] ?? null,
            ]);
        }
    }

    // ── Confirm receipt ──────────────────────────────────────────────────────

    public function confirmReceived(): void
    {
        if (!$this->editingId || empty($this->lines)) {
            $this->dispatch('notify', message: 'Không có sản phẩm nào để xác nhận.', type: 'error');
            return;
        }

        \DB::beginTransaction();
        try {
            foreach ($this->lines as $line) {
                $actual = (int) ($line['actual_quantity'] ?? $line['send_quantity']);
                if ($actual <= 0) continue;

                $fromProduct = Product::find($line['from_product_id']);
                if ($fromProduct) {
                    $beforeFrom = (int) $fromProduct->stock_quantity;
                    $fromProduct->decrement('stock_quantity', $actual);
                    $fromProduct->recordStockHistory(
                        'Transfer', -$actual,
                        $this->editingId, $this->transferCode,
                        'Chuyển hàng đi ' . strtoupper($this->toBranch),
                        $beforeFrom
                    );
                }

                if (!empty($line['to_product_id'])) {
                    $toProduct = Product::find($line['to_product_id']);
                    if ($toProduct) {
                        $beforeTo = (int) $toProduct->stock_quantity;
                        $toProduct->increment('stock_quantity', $actual);
                        $toProduct->recordStockHistory(
                            'Transfer', $actual,
                            $this->editingId, $this->transferCode,
                            'Nhận hàng từ ' . strtoupper($this->fromBranch),
                            $beforeTo
                        );
                    }
                }
            }

            StockTransfer::where('id', $this->editingId)->update([
                'status'       => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
            $this->persistToDb('confirmed');
            $this->status = 'confirmed';

            // Ghi nhật ký hệ thống cho việc gửi/chuyển hàng (ai thực hiện, phiếu nào).
            // Dùng cấu trúc before/after để trang Lịch sử hệ thống hiển thị chi tiết.
            $totalQty = collect($this->lines)->sum(fn($l) => (int) ($l['actual_quantity'] ?? $l['send_quantity'] ?? 0));
            $after = [
                'Mã phiếu'    => $this->transferCode,
                'Tuyến'       => strtoupper($this->fromBranch) . ' → ' . strtoupper($this->toBranch),
                'Số mặt hàng' => (string) count($this->lines),
                'Tổng SL gửi' => (string) $totalQty,
                'Trạng thái'  => 'Đã chuyển',
            ];
            \App\Models\ActivityLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'updated',
                'model_type' => StockTransfer::class,
                'model_id'   => $this->editingId,
                'changes'    => [
                    'before' => array_fill_keys(array_keys($after), ''),
                    'after'  => $after,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            \DB::commit();
            $this->dispatch('notify', message: 'Đã xác nhận nhận hàng! Tồn kho đã được cập nhật.', type: 'success');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function deleteTransfer(int $id): void
    {
        $transfer = StockTransfer::find($id);
        if (!$transfer || $transfer->status === 'confirmed') {
            $this->dispatch('notify', message: 'Không thể xóa phiếu đã xác nhận.', type: 'error');
            return;
        }
        $transfer->items()->delete();
        $transfer->delete();
        $this->dispatch('notify', message: 'Đã xóa phiếu chuyển hàng.', type: 'success');
        $this->resetPage();
    }

    // ── Suggestions ──────────────────────────────────────────────────────────

    public function getSuggestionsProperty(): array
    {
        if ($this->mode !== 'edit') return [];

        $existingIds = array_column($this->lines, 'from_product_id');

        // Get all products that have related_sku matching another product
        $products = Product::whereNotNull('related_sku')
            ->whereNotIn('id', $existingIds)
            ->orderBy('sku')
            ->take(100)
            ->get(['id', 'sku', 'base_name', 'name', 'stock_quantity', 'images', 'related_sku']);

        $relatedSkus = $products->pluck('related_sku')->filter()->unique()->values()->toArray();
        $relatedMap  = Product::whereIn('sku', $relatedSkus)
            ->get(['id', 'sku', 'stock_quantity'])
            ->keyBy('sku');

        return $products
            ->map(function ($p) use ($relatedMap) {
                $related = $relatedMap->get($p->related_sku);
                if (!$related) return null;

                return [
                    'id'          => $p->id,
                    'sku'         => $p->sku,
                    'name'        => $p->base_name ?: $p->name,
                    'from_stock'  => $p->stock_quantity,
                    'to_stock'    => $related->stock_quantity,
                    'related_sku' => $related->sku,
                    'imbalance'   => abs($p->stock_quantity - $related->stock_quantity),
                    'image'       => $p->images[0] ?? null,
                ];
            })
            ->filter()
            ->sortByDesc('imbalance')
            ->values()
            ->toArray();
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $data = ['suggestions' => $this->suggestions];

        if ($this->mode === 'list') {
            $data['transfers'] = StockTransfer::with('createdBy')->withCount('items')
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->when($this->branchFilter !== 'all', fn($q) => $q->where(function ($q) {
                    $q->where('from_branch', $this->branchFilter)
                      ->orWhere('to_branch', $this->branchFilter);
                }))
                ->latest()
                ->paginate(20);
        }

        return view('livewire.product.stock-transfer-index', $data)->layout('layouts.app');
    }
}
