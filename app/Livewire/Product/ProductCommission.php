<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Traits\WithBulkActions;
use App\Imports\CommissionImport;
use App\Exports\CommissionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\HasPermissions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;
use App\Traits\WithChunkedImport;
use App\Exports\CommissionTemplateExport;

class ProductCommission extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences, WithChunkedImport;

    protected function getModuleKey(): string
    {
        return 'commissions';
    }

    public $search = '';
    public $perPage = 15;

    // Cột chọn khi export (mặc định = tất cả).
    public array $exportColumns = ['sku', 'name', 'sale_price', 'cost_price', 'commission_type', 'commission', 'commission_value'];

    protected function getDefaultVisibleColumns(): array
    {
        return ['sku', 'name', 'unit', 'sale_price', 'cost_price', 'profit', 'commission'];
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updateCommission($productId, $amount)
    {
        if (!$this->guardCommissionEdit()) {
            return;
        }
        Product::where('id', $productId)->update([
            'commission_type'   => 'amount',
            'commission_amount' => (int) $amount,
            'commission_percent' => 0,
        ]);
        $this->dispatch('notify', message: 'Cập nhật hoa hồng thành công!', type: 'success');
    }

    /** Đổi loại hoa hồng (amount|percent) cho 1 sản phẩm. */
    public function updateCommissionType($productId, $type)
    {
        if (!$this->guardCommissionEdit()) {
            return;
        }
        $type = in_array($type, ['amount', 'percent'], true) ? $type : 'amount';
        $product = Product::find($productId);
        if (!$product) {
            return;
        }
        $product->commission_type = $type;
        // Đồng bộ commission_amount (tiền) để các nơi đọc trực tiếp vẫn đúng.
        $product->commission_amount = (int) $product->commission_value;
        $product->save();
    }

    /** Cập nhật % hoa hồng (loại percent) cho 1 sản phẩm. */
    public function updateCommissionPercent($productId, $percent)
    {
        if (!$this->guardCommissionEdit()) {
            return;
        }
        $percent = max(0, min(100, (float) $percent));
        $product = Product::find($productId);
        if (!$product) {
            return;
        }
        $product->commission_type = 'percent';
        $product->commission_percent = $percent;
        $product->commission_amount = (int) round(((float) $product->sale_price) * $percent / 100);
        $product->save();
        $this->dispatch('notify', message: 'Cập nhật hoa hồng (%) thành công!', type: 'success');
    }

    protected function guardCommissionEdit(): bool
    {
        if (!auth()->user()->hasPermission('commission.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền sửa hoa hồng!', type: 'error');
            return false;
        }
        return true;
    }

    public function syncCommissions()
    {
        if (!auth()->user()->hasPermission('commission.sync')) {
            $this->dispatch('notify', message: 'Bạn không có quyền đồng bộ dữ liệu!', type: 'error');
            return;
        }

        \DB::transaction(function () {
            // Lấy tất cả sản phẩm (bao gồm cả hàng đã xóa nếu cần đối soát đơn cũ)
            $products = Product::withTrashed()->get()->mapWithKeys(function ($item) {
                return [strtoupper(trim((string)$item->sku)) => $item];
            });

            \Log::info("SyncCommissions: Loaded " . $products->count() . " products (including trashed) for mapping.");
            
            // Lấy tất cả hóa đơn không bị hủy
            $invoices = Invoice::with(['items', 'user'])->get();
            $invoiceCount = 0;
            $itemCount = 0;
            $matchCount = 0;

            foreach ($invoices as $invoice) {
                $status = strtolower($invoice->status);
                if (in_array($status, ['cancelled', 'đã hủy', 'hủy'])) {
                    continue;
                }

                $seller = $invoice->user;
                $canReceiveCommission = $seller ? (bool)$seller->can_receive_commission : true;
                $invoiceTotalCommission = 0;

                foreach ($invoice->items as $item) {
                    $itemSku = strtoupper(trim((string)$item->sku));
                    $product = $products[$itemSku] ?? null;
                    
                    if ($product) {
                        $matchCount++;
                        // Lấy giá trị hoa hồng chuẩn từ "Bảng hoa hồng chung" (đã quy đổi ra tiền nếu là %)
                        $standardRate = (float)$product->commission_value;
                        $targetRate = $canReceiveCommission ? $standardRate : 0;

                        if (round((float)$item->commission_amount, 2) !== round((float)$targetRate, 2)) {
                            \Log::info("SyncCommissions: [MATCHED] SKU [{$itemSku}] in Invoice [{$invoice->invoice_code}]. Rate: Product={$standardRate}, Item Old={$item->commission_amount}, New={$targetRate}");
                            $item->commission_amount = $targetRate;
                            $item->save();
                            $itemCount++;
                        }
                    } else {
                        \Log::warning("SyncCommissions: [NO MATCH] SKU [{$itemSku}] in Invoice [{$invoice->invoice_code}] not found in Products table.");
                        $targetRate = (float)$item->commission_amount;
                    }

                    $invoiceTotalCommission += ($targetRate * $item->quantity);
                }

                if (round((float)$invoice->total_commission, 2) !== round((float)$invoiceTotalCommission, 2)) {
                    \Log::info("SyncCommissions: Updating Invoice [{$invoice->invoice_code}] total. Old: {$invoice->total_commission}, New: {$invoiceTotalCommission}");
                    $invoice->total_commission = $invoiceTotalCommission;
                    $invoice->save();
                    $invoiceCount++;
                }
            }

            \Log::info("SyncCommissions: Completed. Total Matches: {$matchCount}, Updated {$invoiceCount} invoices and {$itemCount} items.");
            $this->dispatch('notify', message: "Đồng bộ hoàn tất! Tìm thấy {$matchCount} mặt hàng khớp mã. Đã cập nhật {$invoiceCount} hóa đơn. Vui lòng kiểm tra log nếu số lượng cập nhật là 0.", type: 'success');
        });
    }

    /* ===== Import hoa hồng đồng bộ theo chunk (WithChunkedImport) ===== */

    protected function authorizeImport(): bool
    {
        if (!auth()->user()->hasPermission('commission.import')) {
            $this->dispatch('notify', message: 'Bạn không có quyền nhập Excel!', type: 'error');
            return false;
        }
        return true;
    }

    protected function importFinishedId(): string
    {
        return 'commissions';
    }

    protected function importChunkSize(): int
    {
        return 50;
    }

    /** Đọc file hoa hồng: chuẩn hoá (fix KiotViet) rồi đọc từ dòng 3 (không heading). */
    protected function readImportRows(string $absolutePath): array
    {
        $normalizedPath = $this->normalizeExcelFile($absolutePath);
        try {
            $reader = new class implements \Maatwebsite\Excel\Concerns\ToArray, \Maatwebsite\Excel\Concerns\WithStartRow {
                public function array(array $array)
                {
                    return $array;
                }

                public function startRow(): int
                {
                    return 3;
                }
            };
            $data = Excel::toArray($reader, $normalizedPath);
            return $data[0] ?? [];
        } finally {
            if ($normalizedPath !== $absolutePath) {
                @unlink($normalizedPath);
            }
        }
    }

    /** Cập nhật hoa hồng 1 sản phẩm theo 1 dòng (cột số: A=SKU, E=loại, F=giá trị, G=tiền cũ). */
    protected function importOneRow(array $row, int $rowNumber): void
    {
        $sku = trim((string) ($row[0] ?? ''));
        if ($sku === '') {
            throw new \RuntimeException('Thiếu mã hàng.');
        }

        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            throw new \RuntimeException("SKU \"{$sku}\" không tồn tại.");
        }

        $typeCell = strtolower(trim((string) ($row[4] ?? '')));
        $isPercent = in_array($typeCell, ['%', 'percent', 'phan tram', 'phần trăm'], true);
        $isAmount = in_array($typeCell, ['tien', 'tiền', 'amount', 'vnd', 'vnđ'], true);

        if ($isPercent) {
            $pct = max(0, min(100, (float) str_replace(',', '.', (string) ($row[5] ?? 0))));
            $product->commission_type = 'percent';
            $product->commission_percent = $pct;
            $product->commission_amount = (int) round(((float) $product->sale_price) * $pct / 100);
        } else {
            $raw = $isAmount ? ($row[5] ?? 0) : ($row[6] ?? ($row[5] ?? 0));
            $clean = str_replace([',', '.'], '', (string) $raw);
            $product->commission_type = 'amount';
            $product->commission_amount = (int) $clean;
            $product->commission_percent = 0;
        }
        $product->save();
    }

    /** Chuẩn hóa file Excel (load + save lại) để fix XML không chuẩn của KiotViet. */
    private function normalizeExcelFile(string $path): string
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $normalizedPath = $path . '_normalized.xlsx';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($normalizedPath);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            return $normalizedPath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not normalize Excel file: ' . $e->getMessage());
            return $path;
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new CommissionTemplateExport, 'mau-bang-hoa-hong.xlsx');
    }

    public function export()
    {
        if (!auth()->user()->hasPermission('commission.export')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xuất file!', type: 'error');
            return;
        }
        $columns = !empty($this->exportColumns) ? array_values($this->exportColumns) : null;
        return Excel::download(new CommissionExport($columns), 'bang-hoa-hong-' . date('Y-m-d') . '.xlsx');
    }

    protected function getRecordsForBulk()
    {
        return Product::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $search = $this->search;
                    $q->whereRaw("sku REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)'])
                      ->orWhereRaw("location REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)'])
                      ->orWhereRaw("name REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)']);
                });

                $query->orderByRaw("CASE 
                        WHEN sku = ? THEN 1 
                        WHEN location = ? THEN 2
                        WHEN sku LIKE ? THEN 3 
                        WHEN name LIKE ? THEN 4 
                        ELSE 5 
                    END", [$this->search, $this->search, $this->search . '%', $this->search . '%']);
            })
            ->orderBy('sku', 'asc')
            ->get();
    }

    protected function getModelForBulk()
    {
        return Product::class;
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $search = $this->search;
                    $q->whereRaw("sku REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)'])
                      ->orWhereRaw("location REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)'])
                      ->orWhereRaw("name REGEXP ?", ['(^|[^0-9])' . $search . '([^0-9]|$)']);
                });

                $query->orderByRaw("CASE 
                        WHEN sku = ? THEN 1 
                        WHEN location = ? THEN 2
                        WHEN sku LIKE ? THEN 3 
                        WHEN name LIKE ? THEN 4 
                        ELSE 5 
                    END", [$this->search, $this->search, $this->search . '%', $this->search . '%']);
            })
            ->orderBy('sku', 'asc')
            ->paginate($this->perPage);

        return view('livewire.product.product-commission', [
            'products' => $products
        ])->layout('layouts.app');
    }
}
