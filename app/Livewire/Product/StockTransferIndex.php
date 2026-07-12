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
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDir = 'desc';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingBranchFilter(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    // ── Edit mode ────────────────────────────────────────────────────────────
    public string $mode = 'list';
    public ?int $editingId = null;
    public string $fromBranch = 'hn';
    public string $toBranch = 'sg';
    public string $transferCode = '';
    public string $status = 'draft';
    public string $notes = '';
    public array $lines = [];
    public ?int $createdBy = null;
    public string $trackingCode = '';   // mã vận đơn ĐVVC (nhập khi gửi hàng)
    public bool $senderConfirmed = false; // bên gửi đã chốt chênh lệch

    // ── Search ───────────────────────────────────────────────────────────────
    public string $productSearch = '';
    public array $searchResults = [];
    public string $suggestionSearch = '';

    public function mount(): void
    {
        $this->resetDirectionFromUser();

        // Điều hướng từ nhật ký/thông báo: /products/transfers?open=ID -> mở thẳng phiếu đó.
        $openId = request()->query('open');
        if ($openId) {
            $this->editTransfer((int) $openId);
        }
    }

    private function resetDirectionFromUser(): void
    {
        $userBranch = auth()->user()?->work_branch ?? 'hn';
        $this->fromBranch = in_array($userBranch, ['hn', 'sg'], true) ? $userBranch : 'hn';
        $this->toBranch   = $this->fromBranch === 'hn' ? 'sg' : 'hn';
    }

    // ── Quy ước chi nhánh theo SKU: SG = tiền tố 'Z', HN = không có ───────────
    private function isSgSku(?string $sku): bool
    {
        return $sku !== null && str_starts_with(trim($sku), 'Z');
    }

    private function counterpartSku(?string $sku): ?string
    {
        $sku = trim((string) $sku);
        if ($sku === '') return null;
        return str_starts_with($sku, 'Z') ? substr($sku, 1) : 'Z' . $sku;
    }

    // ── Quyền ──────────────────────────────────────────────────────────────
    // Bên gửi (người tạo phiếu) được chỉnh khi còn nháp — chọn chiều nào cũng sửa được.
    // Phiếu mới chưa lưu (createdBy null) hoặc nhân viên chi nhánh nguồn cũng được.
    public function getCanEditProperty(): bool
    {
        if ($this->status !== 'draft') return false;
        $u = auth()->user();
        if (!$u) return false;
        return $u->role === 'admin'
            || $this->createdBy === null
            || (int) $this->createdBy === (int) $u->id
            || $u->work_branch === $this->fromBranch;
    }

    // Bên GỬI: admin, người tạo, hoặc nhân viên chi nhánh nguồn.
    private function isSender(): bool
    {
        $u = auth()->user();
        return $u && ($u->role === 'admin'
            || (int) $this->createdBy === (int) $u->id
            || $u->work_branch === $this->fromBranch);
    }

    // Bên NHẬN: admin hoặc nhân viên chi nhánh đích.
    private function isReceiver(): bool
    {
        $u = auth()->user();
        return $u && ($u->role === 'admin' || $u->work_branch === $this->toBranch);
    }

    // Có dòng nào thực nhận khác số gửi không (đã nhập thực nhận).
    public function getHasDiscrepancyProperty(): bool
    {
        foreach ($this->lines as $l) {
            $send = ($l['send_quantity'] === null || $l['send_quantity'] === '') ? 0 : (int) $l['send_quantity'];
            $act  = (isset($l['actual_quantity']) && $l['actual_quantity'] !== null && $l['actual_quantity'] !== '')
                ? (int) $l['actual_quantity'] : null;
            if ($act !== null && $act !== $send) return true;
        }
        return false;
    }

    // Bước 2 — Gửi hàng: phiếu nháp có dòng, do bên gửi thao tác.
    public function getCanShipProperty(): bool
    {
        return $this->status === 'draft' && $this->editingId && !empty($this->lines) && $this->isSender();
    }

    // Bước 3a — Nhận hàng: phiếu đang vận chuyển, bên nhận bấm nhận.
    public function getCanReceiveProperty(): bool
    {
        return $this->status === 'shipping' && $this->isReceiver();
    }

    // Bên nhận được nhập ô "Thực nhận" khi phiếu ở trạng thái đã nhận.
    public function getCanEditActualProperty(): bool
    {
        return $this->status === 'received' && $this->isReceiver();
    }

    // Bên gửi xác nhận đã chốt chênh lệch (mở khoá nút Hoàn thành cho bên nhận).
    public function getCanSenderConfirmProperty(): bool
    {
        return $this->status === 'received' && $this->hasDiscrepancy
            && !$this->senderConfirmed && $this->isSender();
    }

    // Bước 3b — Hoàn thành: khớp từ đầu, hoặc đã lệch nhưng bên gửi đã chốt.
    public function getCanCompleteProperty(): bool
    {
        return $this->status === 'received' && $this->isReceiver()
            && (!$this->hasDiscrepancy || $this->senderConfirmed);
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
        $this->suggestionSearch = '';
        $this->createdBy    = auth()->id();
        $this->resetDirectionFromUser();
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
        $this->createdBy    = $transfer->created_by;
        $this->trackingCode = $transfer->tracking_code ?? '';
        $this->senderConfirmed = $transfer->sender_confirmed_at !== null;

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
        $this->suggestionSearch = '';
        $this->mode = 'edit';
    }

    public function cancelEdit(): void
    {
        $this->mode      = 'list';
        $this->editingId = null;
        $this->lines     = [];
        $this->resetPage();
    }

    // Chọn chiều chuyển bằng radio (from = 'hn' | 'sg'). Chỉ có 2 chi nhánh nên
    // đổi từ = đảo chiều.
    public function setDirection(string $from): void
    {
        if (!$this->canEdit) return;
        $from = in_array($from, ['hn', 'sg'], true) ? $from : 'hn';
        if ($from !== $this->fromBranch) {
            $this->swapDirection();
        }
    }

    // Đổi chiều chuyển (HN→SG ↔ SG→HN). Đảo luôn from/to của từng dòng.
    public function swapDirection(): void
    {
        if (!$this->canEdit) return;

        [$this->fromBranch, $this->toBranch] = [$this->toBranch, $this->fromBranch];

        foreach ($this->lines as $i => $line) {
            $this->lines[$i] = array_merge($line, [
                'from_product_id' => $line['to_product_id'] ?? null,
                'to_product_id'   => $line['from_product_id'] ?? null,
                'from_sku'        => $line['to_sku'] ?? null,
                'to_sku'          => $line['from_sku'] ?? null,
                'from_stock'      => (int) ($line['to_stock'] ?? 0),
                'to_stock'        => (int) ($line['from_stock'] ?? 0),
            ]);
        }

        $this->autoSave();
    }

    // ── Product management ───────────────────────────────────────────────────
    public function addProduct(int $productId): void
    {
        if (!$this->canEdit) {
            $this->dispatch('notify', message: 'Bạn không có quyền chỉnh phiếu này.', type: 'error');
            return;
        }

        $product = Product::find($productId);
        if (!$product) return;

        // Quy sản phẩm về đúng chi nhánh NGUỒN theo chiều đang chọn.
        $fromIsSg = $this->fromBranch === 'sg';
        if ($this->isSgSku($product->sku) !== $fromIsSg) {
            $cpSku = $this->counterpartSku($product->sku);
            $product = $cpSku ? Product::where('sku', $cpSku)->first() : null;
        }
        if (!$product) {
            $this->dispatch('notify', message: 'Không tìm thấy sản phẩm ở chi nhánh nguồn.', type: 'error');
            return;
        }

        foreach ($this->lines as $line) {
            if ((int) $line['from_product_id'] === (int) $product->id) {
                $this->dispatch('notify', message: 'Sản phẩm đã có trong danh sách.', type: 'warning');
                $this->productSearch = '';
                $this->searchResults = [];
                return;
            }
        }

        $toSku = $this->counterpartSku($product->sku);
        $toProduct = $toSku ? Product::where('sku', $toSku)->first() : null;
        if (!$toProduct) {
            $this->dispatch('notify', message: 'Sản phẩm chưa có ở chi nhánh ' . strtoupper($this->toBranch) . ', không thể chuyển.', type: 'warning');
            return;
        }

        $this->lines[] = [
            'from_product_id' => $product->id,
            'to_product_id'   => $toProduct->id,
            'from_sku'        => $product->sku,
            'to_sku'          => $toProduct->sku,
            'product_name'    => $product->base_name ?: $product->name,
            'image'           => $product->images[0] ?? null,
            'from_stock'      => (int) $product->stock_quantity,
            'to_stock'        => (int) $toProduct->stock_quantity,
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
        if (!$this->canEdit) return;
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
        $this->autoSave();
    }

    public function updatedLines(): void
    {
        if ($this->canEdit) {          // nháp: bên gửi sửa số gửi
            $this->autoSave();
            return;
        }
        if ($this->canEditActual) {    // đã nhận: bên nhận nhập thực nhận / lý do
            $this->saveActuals();
        }
    }

    // Lưu riêng ô "Thực nhận" + "Lý do" mà KHÔNG xoá/tạo lại dòng (an toàn ở bước nhận).
    private function saveActuals(): void
    {
        if (!$this->editingId) return;
        $transfer = StockTransfer::with('items')->find($this->editingId);
        if (!$transfer) return;

        foreach ($this->lines as $line) {
            $item = $transfer->items->firstWhere('from_product_id', $line['from_product_id']);
            if (!$item) continue;
            $act = (isset($line['actual_quantity']) && $line['actual_quantity'] !== null && $line['actual_quantity'] !== '')
                ? max(0, (int) $line['actual_quantity']) : null;
            $item->update([
                'actual_quantity' => $act,
                'adjust_reason'   => $line['adjust_reason'] ?? null,
            ]);
        }
        // Bên nhận vừa sửa lệch -> cần bên gửi xác nhận lại.
        if ($transfer->sender_confirmed_at !== null && $this->hasDiscrepancy) {
            $transfer->update(['sender_confirmed_at' => null, 'sender_confirmed_by' => null]);
            $this->senderConfirmed = false;
        }
    }

    public function updatedProductSearch(): void
    {
        if (!$this->canEdit || strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $existingIds = array_column($this->lines, 'from_product_id');
        $fromIsSg = $this->fromBranch === 'sg';

        $results = Product::query()
            ->where(function ($q) {
                $q->where('sku', 'like', "%{$this->productSearch}%")
                  ->orWhere('base_name', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->when($fromIsSg, fn($q) => $q->where('sku', 'like', 'Z%'))
            ->when(!$fromIsSg, fn($q) => $q->where('sku', 'not like', 'Z%'))
            ->whereNotIn('id', $existingIds)
            ->orderBy('sku')
            ->take(12)
            ->get(['id', 'sku', 'base_name', 'name', 'stock_quantity', 'images']);

        $cpSkus = $results->map(fn($p) => $this->counterpartSku($p->sku))->filter()->unique()->values()->all();
        $cpMap  = Product::whereIn('sku', $cpSkus)->get(['sku', 'stock_quantity'])->keyBy('sku');

        $this->searchResults = $results->map(function ($p) use ($cpMap) {
            $cp = $cpMap->get($this->counterpartSku($p->sku));
            return [
                'id'            => $p->id,
                'sku'           => $p->sku,
                'name'          => $p->base_name ?: $p->name,
                'stock'         => (int) $p->stock_quantity,
                'related_sku'   => $cp?->sku,
                'related_stock' => $cp ? (int) $cp->stock_quantity : null,
                'image'         => $p->images[0] ?? null,
            ];
        })->toArray();
    }

    // ── Persist ──────────────────────────────────────────────────────────────
    public function autoSave(): void
    {
        if (!$this->canEdit) return;
        if (empty($this->lines) && !$this->editingId) return;
        $this->persistToDb($this->status);
    }

    public function saveDraft(): void
    {
        if (!$this->canEdit) {
            $this->dispatch('notify', message: 'Bạn không có quyền lưu phiếu này.', type: 'error');
            return;
        }
        $this->persistToDb('draft');
        $this->dispatch('notify', message: 'Đã lưu tạm phiếu chuyển hàng.', type: 'success');
        // Lưu tạm xong -> quay lại danh sách phiếu chuyển hàng.
        $this->cancelEdit();
    }

    private function persistToDb(string $status): void
    {
        $data = [
            'from_branch' => $this->fromBranch,
            'to_branch'   => $this->toBranch,
            'status'      => $status,
            'notes'       => $this->notes,
        ];

        if ($this->editingId) {
            $transfer = StockTransfer::find($this->editingId);
            if (!$transfer) return;
            $transfer->update($data); // KHÔNG đổi created_by — giữ nguyên người tạo (bên gửi)
        } else {
            $code     = 'CK-' . strtoupper($this->fromBranch) . strtoupper($this->toBranch) . '-' . date('ymdHis');
            $transfer = StockTransfer::create(array_merge($data, ['code' => $code, 'created_by' => auth()->id()]));
            $this->editingId    = $transfer->id;
            $this->transferCode = $transfer->code;
            $this->createdBy    = $transfer->created_by;

            // Ghi nhật ký mốc "tạo phiếu gửi hàng" (1 lần).
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'model_type' => StockTransfer::class,
                'model_id' => $transfer->id,
                'changes' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
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
                // Để TRỐNG nếu chưa nhập (không tự về 0/1) -> mở lại phiếu vẫn trống + báo đỏ.
                'send_quantity'   => ($line['send_quantity'] === null || $line['send_quantity'] === '')
                    ? null
                    : max(0, (int) $line['send_quantity']),
                'actual_quantity' => isset($line['actual_quantity']) && $line['actual_quantity'] !== null
                    ? (int) $line['actual_quantity']
                    : null,
                'adjust_reason'   => $line['adjust_reason'] ?? null,
            ]);
        }
    }

    // ── Bước 2: Gửi hàng (bên gửi) — trừ tồn nguồn NGAY + gắn mã vận đơn ──────
    public function shipGoods(): void
    {
        if (!$this->canShip) {
            $this->dispatch('notify', message: 'Chỉ bên gửi mới được gửi hàng.', type: 'error');
            return;
        }
        foreach ($this->lines as $l) {
            $send = ($l['send_quantity'] === null || $l['send_quantity'] === '') ? 0 : (int) $l['send_quantity'];
            if ($send <= 0) {
                $this->dispatch('notify', message: 'Vui lòng nhập số lượng gửi (> 0) cho tất cả sản phẩm.', type: 'warning');
                return;
            }
        }
        if (trim($this->trackingCode) === '') {
            $this->dispatch('notify', message: 'Vui lòng nhập mã vận đơn của đơn vị vận chuyển.', type: 'warning');
            return;
        }

        \DB::beginTransaction();
        try {
            $this->persistToDb('draft'); // chốt item mới nhất trước khi trừ kho
            foreach ($this->lines as $line) {
                $send = (int) $line['send_quantity'];
                if ($send <= 0) continue;
                $fromProduct = Product::find($line['from_product_id']);
                if ($fromProduct) {
                    $before = (int) $fromProduct->stock_quantity;
                    $fromProduct->decrement('stock_quantity', $send);
                    $fromProduct->recordStockHistory(
                        'Transfer', -$send, $this->editingId, $this->transferCode,
                        'Gửi hàng đi ' . strtoupper($this->toBranch) . ' (vận đơn ' . trim($this->trackingCode) . ')',
                        $before
                    );
                }
            }
            StockTransfer::where('id', $this->editingId)->update([
                'status'        => 'shipping',
                'tracking_code' => trim($this->trackingCode),
                'shipped_at'    => now(),
                'shipped_by'    => auth()->id(),
            ]);
            $this->status = 'shipping';
            $this->logStatus('Đang vận chuyển', ['Mã vận đơn' => trim($this->trackingCode)]);
            \DB::commit();
            $this->dispatch('notify', message: 'Đã gửi hàng! Tồn kho chi nhánh gửi đã được trừ.', type: 'success');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }

    // ── Bước 3a: Nhận hàng (bên nhận) — mở ô Thực nhận ────────────────────────
    public function receiveGoods(): void
    {
        if (!$this->canReceive) {
            $this->dispatch('notify', message: 'Chỉ chi nhánh ' . strtoupper($this->toBranch) . ' mới được nhận hàng.', type: 'error');
            return;
        }
        StockTransfer::where('id', $this->editingId)->update([
            'status'      => 'received',
            'received_at' => now(),
            'received_by' => auth()->id(),
        ]);
        $this->status = 'received';
        $this->logStatus('Đã nhận · chờ xác nhận', []);
        $this->dispatch('notify', message: 'Đã nhận hàng. Vui lòng nhập số thực nhận cho từng sản phẩm.', type: 'success');
    }

    // ── Bên gửi chốt chênh lệch (mở khoá nút Hoàn thành khi lệch) ─────────────
    public function senderConfirm(): void
    {
        if (!$this->canSenderConfirm) {
            $this->dispatch('notify', message: 'Không thể xác nhận lúc này.', type: 'error');
            return;
        }
        StockTransfer::where('id', $this->editingId)->update([
            'sender_confirmed_at' => now(),
            'sender_confirmed_by' => auth()->id(),
        ]);
        $this->senderConfirmed = true;
        $this->logStatus('Bên gửi đã chốt chênh lệch', []);
        $this->dispatch('notify', message: 'Đã chốt chênh lệch. Bên nhận có thể hoàn thành phiếu.', type: 'success');
    }

    // ── Bước 3b: Hoàn thành (bên nhận) — cộng tồn nhận + cộng/trừ bù nguồn ────
    public function completeTransfer(): void
    {
        if (!$this->canComplete) {
            $msg = $this->hasDiscrepancy && !$this->senderConfirmed
                ? 'Thực nhận đang lệch số gửi — chờ bên gửi xác nhận trước khi hoàn thành.'
                : 'Bạn không thể hoàn thành phiếu này.';
            $this->dispatch('notify', message: $msg, type: 'warning');
            return;
        }

        \DB::beginTransaction();
        try {
            $this->saveActuals(); // chốt thực nhận mới nhất

            foreach ($this->lines as $line) {
                $send   = ($line['send_quantity'] === null || $line['send_quantity'] === '') ? 0 : (int) $line['send_quantity'];
                $actual = (isset($line['actual_quantity']) && $line['actual_quantity'] !== null && $line['actual_quantity'] !== '')
                    ? max(0, (int) $line['actual_quantity']) : $send;

                // Cộng tồn chi nhánh NHẬN theo số thực nhận.
                if ($actual > 0 && !empty($line['to_product_id'])) {
                    $toProduct = Product::find($line['to_product_id']);
                    if ($toProduct) {
                        $beforeTo = (int) $toProduct->stock_quantity;
                        $toProduct->increment('stock_quantity', $actual);
                        $toProduct->recordStockHistory(
                            'Transfer', $actual, $this->editingId, $this->transferCode,
                            'Nhận hàng từ ' . strtoupper($this->fromBranch),
                            $beforeTo
                        );
                    }
                }

                // Bù chênh lệch cho chi nhánh GỬI: đã trừ $send lúc gửi, chỉ $actual tới nơi.
                $diff = $send - $actual; // >0: thiếu -> cộng bù nguồn; <0: dư -> trừ thêm nguồn
                if ($diff !== 0) {
                    $fromProduct = Product::find($line['from_product_id']);
                    if ($fromProduct) {
                        $beforeFrom = (int) $fromProduct->stock_quantity;
                        $fromProduct->increment('stock_quantity', $diff);
                        $fromProduct->recordStockHistory(
                            'Adjustment', $diff, $this->editingId, $this->transferCode,
                            'Bù chênh lệch chuyển hàng (' . strtoupper($this->fromBranch) . '→' . strtoupper($this->toBranch) . ', thực nhận ' . $actual . '/' . $send . ')',
                            $beforeFrom
                        );
                    }
                }
            }

            StockTransfer::where('id', $this->editingId)->update([
                'status'       => 'completed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
            $this->status = 'completed';
            $this->logStatus('Đã hoàn thành', []);
            \DB::commit();
            $this->dispatch('notify', message: 'Đã hoàn thành phiếu! Tồn kho hai chi nhánh đã cập nhật.', type: 'success');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }

    // Ghi nhật ký hệ thống cho các mốc trạng thái chuyển hàng.
    private function logStatus(string $stateLabel, array $extra): void
    {
        $after = array_merge([
            'Mã phiếu'   => $this->transferCode,
            'Tuyến'      => strtoupper($this->fromBranch) . ' → ' . strtoupper($this->toBranch),
            'Trạng thái' => $stateLabel,
        ], $extra);
        \App\Models\ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'updated',
            'model_type' => StockTransfer::class,
            'model_id'   => $this->editingId,
            'changes'    => ['before' => array_fill_keys(array_keys($after), ''), 'after' => $after],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ── Delete ───────────────────────────────────────────────────────────────
    public function deleteTransfer(int $id): void
    {
        $transfer = StockTransfer::find($id);
        if (!$transfer || $transfer->status !== 'draft') {
            $this->dispatch('notify', message: 'Chỉ xóa được phiếu ở trạng thái Nháp.', type: 'error');
            return;
        }
        $code = $transfer->code;
        $transfer->items()->delete();
        $transfer->delete();

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => StockTransfer::class,
            'model_id' => $id,
            'changes' => ['after' => ['Mã phiếu' => $code], 'before' => ['Mã phiếu' => '']],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->dispatch('notify', message: 'Đã xóa phiếu chuyển hàng.', type: 'success');
        $this->resetPage();
    }

    // ── Suggestions (lệch tồn 2 chi nhánh, mỗi cặp 1 lần, lệch ≥ 1) ───────────
    public function getSuggestionsProperty(): array
    {
        if ($this->mode !== 'edit' || !$this->canEdit) return [];

        $fromIsSg = $this->fromBranch === 'sg';

        $fromProducts = Product::query()
            ->when($fromIsSg, fn($q) => $q->where('sku', 'like', 'Z%'))
            ->when(!$fromIsSg, fn($q) => $q->where('sku', 'not like', 'Z%'))
            ->when(strlen(trim($this->suggestionSearch)) >= 1, function ($q) {
                $kw = trim($this->suggestionSearch);
                $q->where(function ($w) use ($kw) {
                    $w->where('sku', 'like', "%{$kw}%")
                      ->orWhere('base_name', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
                });
            })
            ->orderBy('sku')
            ->get(['id', 'sku', 'base_name', 'name', 'stock_quantity', 'images']);

        $cpSkus = $fromProducts->map(fn($p) => $this->counterpartSku($p->sku))->filter()->unique()->values()->all();
        // Ghép cặp KHÔNG phân biệt hoa/thường + trim (SKU lệch case trước đây bị mất cặp -> gợi ý bỏ sót).
        $cpMap  = Product::whereIn('sku', $cpSkus)->get(['id', 'sku', 'stock_quantity'])
            ->keyBy(fn($p) => strtoupper(trim((string) $p->sku)));

        $existingIds = array_column($this->lines, 'from_product_id');

        return $fromProducts
            ->map(function ($p) use ($cpMap, $existingIds) {
                $cp = $cpMap->get(strtoupper(trim((string) $this->counterpartSku($p->sku))));
                if (!$cp) return null;                        // chưa có cặp ở chi nhánh đối -> bỏ
                if (in_array($p->id, $existingIds)) return null;

                $imbalance = abs((int) $p->stock_quantity - (int) $cp->stock_quantity);
                if ($imbalance < 1) return null;              // lệch < 1 -> bỏ

                return [
                    'id'         => $p->id,                   // id sp NGUỒN (đúng chiều)
                    'sku'        => $p->sku,                  // SKU nguồn
                    'to_sku'     => $cp->sku,                 // SKU chi nhánh đối diện
                    'name'       => $p->base_name ?: $p->name,
                    'from_stock' => (int) $p->stock_quantity,
                    'to_stock'   => (int) $cp->stock_quantity,
                    'imbalance'  => $imbalance,
                    'image'      => $p->images[0] ?? null,
                ];
            })
            ->filter()
            ->sortByDesc('imbalance')
            ->take(100)
            ->values()
            ->toArray();
    }

    // ── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $data = ['suggestions' => $this->suggestions];

        if ($this->mode === 'list') {
            $sortField = in_array($this->sortField, ['created_at', 'code', 'status'], true) ? $this->sortField : 'created_at';
            $sortDir   = $this->sortDir === 'asc' ? 'asc' : 'desc';

            $data['transfers'] = StockTransfer::with('createdBy')->withCount('items')
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->when($this->branchFilter !== 'all', fn($q) => $q->where(function ($q) {
                    $q->where('from_branch', $this->branchFilter)
                      ->orWhere('to_branch', $this->branchFilter);
                }))
                ->when(trim($this->search) !== '', function ($q) {
                    $kw = '%' . trim($this->search) . '%';
                    $q->where(function ($w) use ($kw) {
                        $w->where('code', 'like', $kw)
                          ->orWhere('tracking_code', 'like', $kw)
                          ->orWhereHas('createdBy', fn($u) => $u->where('name', 'like', $kw))
                          ->orWhereHas('items', fn($i) => $i->where('from_sku', 'like', $kw)
                              ->orWhere('to_sku', 'like', $kw)
                              ->orWhere('product_name', 'like', $kw));
                    });
                })
                ->orderBy($sortField, $sortDir)
                ->paginate(20);
        }

        return view('livewire.product.stock-transfer-index', $data)->layout('layouts.app');
    }
}
