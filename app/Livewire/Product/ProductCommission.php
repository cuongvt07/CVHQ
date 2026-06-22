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

class ProductCommission extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'commissions';
    }

    public $search = '';
    public $perPage = 15;
    public $importFile;

    protected function getDefaultVisibleColumns(): array
    {
        return ['sku', 'name', 'unit', 'sale_price', 'cost_price', 'profit', 'commission'];
    }

    // Import Properties (for modal compatibility)
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

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
        if (!auth()->user()->hasPermission('commission.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền sửa hoa hồng!', type: 'error');
            return;
        }
        Product::where('id', $productId)->update(['commission_amount' => $amount]);
        $this->dispatch('notify', message: 'Cập nhật hoa hồng thành công!', type: 'success');
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
                        // Lấy giá trị hoa hồng chuẩn từ "Bảng hoa hồng chung"
                        $standardRate = (float)$product->commission_amount;
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

    public function import()
    {
        if (!auth()->user()->hasPermission('commission.import')) {
            $this->dispatch('notify', message: 'Bạn không có quyền nhập Excel!', type: 'error');
            return;
        }
        set_time_limit(300);

        $this->validate([
            'importFile' => 'required',
        ]);

        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            // Lấy đường dẫn file tạm từ upload
            $tempPath = $this->importFile->getRealPath();

            // Chuẩn hóa file Excel (fix định dạng KiotViet không chuẩn)
            $normalizedPath = $this->normalizeExcelFile($tempPath);

            // Import đồng bộ (file hoa hồng nhỏ, không cần queue)
            $import = new CommissionImport();
            Excel::import($import, $normalizedPath, null, \Maatwebsite\Excel\Excel::XLSX);

            $this->importing = false;
            $this->importProgress = 100;

            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            $this->importErrors = $import->getErrors();

            $this->dispatch('notify', message: "Import hoàn tất! Cập nhật: {$updated}, Bỏ qua: {$skipped}", type: 'success');
            $this->dispatch('import-finished', id: 'commissions');
            $this->importFile = null;

            // Dọn file chuẩn hóa
            if ($normalizedPath !== $tempPath) {
                @unlink($normalizedPath);
            }
        } catch (\Exception $e) {
            $this->importing = false;
            $this->dispatch('notify', message: 'Lỗi import: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Chuẩn hóa file Excel bằng cách load rồi save lại
     * Giải quyết vấn đề file KiotViet có cấu trúc XML không chuẩn
     */
    private function normalizeExcelFile(string $path): string
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $normalizedPath = str_replace('.xlsx', '_normalized.xlsx', $path);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($normalizedPath);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            return $normalizedPath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not normalize Excel file: ' . $e->getMessage());
            return $path;
        }
    }

    public function pollImportProgress()
    {
        if (!$this->importing) return;

        $progress = Cache::get("import_progress_{$this->importBatchId}");

        if ($progress) {
            $this->importTotal = $progress['total'];
            $this->importCurrent = $progress['current'];
            
            if ($this->importTotal > 0) {
                $this->importProgress = min(100, round(($this->importCurrent / $this->importTotal) * 100));
            }

            if ($this->importCurrent >= $this->importTotal || $progress['status'] === 'failed' || $progress['status'] === 'finished') {
                $this->importing = false;
                $this->importErrors = array_merge($this->importErrors, $progress['errors']);
                
                if (empty($this->importErrors)) {
                    $this->dispatch('notify', message: 'Import hoa hồng hoàn tất!', type: 'success');
                }
                
                $this->dispatch('import-finished', id: 'commissions');
            }
        }
    }

    public function export()
    {
        if (!auth()->user()->hasPermission('commission.export')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xuất file!', type: 'error');
            return;
        }
        return Excel::download(new CommissionExport, 'bang-hoa-hong-' . date('Y-m-d') . '.xlsx');
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
