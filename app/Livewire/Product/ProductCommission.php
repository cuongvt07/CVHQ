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

class ProductCommission extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions;

    protected function getModuleKey(): string
    {
        return 'commissions';
    }

    public $search = '';
    public $perPage = 15;
    public $importFile;

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
        Product::where('id', $productId)->update(['commission_amount' => $amount]);
        $this->dispatch('notify', message: 'Cập nhật hoa hồng thành công!', type: 'success');
    }

    public function syncCommissions()
    {
        if (!auth()->user()->hasPermission('invoice.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền đồng bộ dữ liệu!', type: 'error');
            return;
        }

        \DB::transaction(function () {
            // Lấy tất cả sản phẩm, chuẩn hóa SKU (viết hoa + trim) làm key để đối chiếu chính xác nhất
            $products = Product::all()->mapWithKeys(function ($item) {
                return [strtoupper(trim((string)$item->sku)) => $item];
            });
            
            // Lấy tất cả hóa đơn (bao gồm cả các trạng thái khác nhau, chỉ trừ Cancelled nếu muốn)
            // Ở đây tôi lấy hết để đảm bảo không sót đơn nào
            $invoices = Invoice::with(['items', 'user'])->get();
            $invoiceCount = 0;
            $itemCount = 0;

            foreach ($invoices as $invoice) {
                // Chỉ xử lý các đơn không bị hủy
                if (in_array(strtolower($invoice->status), ['cancelled', 'đã hủy', 'hủy'])) {
                    continue;
                }

                $seller = $invoice->user;
                // Nếu không có thông tin seller, mặc định là có thể nhận hoa hồng (admin/system)
                $canReceiveCommission = $seller ? (bool)$seller->can_receive_commission : true;
                $invoiceTotalCommission = 0;

                foreach ($invoice->items as $item) {
                    $itemSku = strtoupper(trim((string)$item->sku));
                    // Tìm sản phẩm hiện tại dựa trên SKU chuẩn hóa
                    $product = $products[$itemSku] ?? null;
                    
                    // Lấy giá trị hoa hồng chuẩn từ "Bảng hoa hồng chung"
                    // Nếu không tìm thấy sản phẩm trong kho hiện tại, giữ nguyên giá trị cũ của đơn hàng
                    $standardRate = $product ? (float)$product->commission_amount : (float)$item->commission_amount;
                    
                    // Áp dụng điều kiện nhân viên (có được hưởng hoa hồng không)
                    $targetRate = $canReceiveCommission ? $standardRate : 0;

                    // So sánh chính xác dạng số (float)
                    if (round((float)$item->commission_amount, 2) !== round((float)$targetRate, 2)) {
                        $item->commission_amount = $targetRate;
                        $item->save();
                        $itemCount++;
                    }

                    $invoiceTotalCommission += ($targetRate * $item->quantity);
                }

                if (round((float)$invoice->total_commission, 2) !== round((float)$invoiceTotalCommission, 2)) {
                    $invoice->total_commission = $invoiceTotalCommission;
                    $invoice->save();
                    $invoiceCount++;
                }
            }

            $this->dispatch('notify', message: "Đồng bộ hoàn tất! Đã cập nhật {$invoiceCount} hóa đơn ({$itemCount} mặt hàng) dựa trên Bảng hoa hồng chung.", type: 'success');
        });
    }

    public function import()
    {
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
        return Excel::download(new CommissionExport, 'bang-hoa-hong-' . date('Y-m-d') . '.xlsx');
    }

    protected function getRecordsForBulk()
    {
        return Product::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('sku', 'like', "%{$this->search}%");
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
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->orderBy('sku', 'asc')
            ->paginate($this->perPage);

        return view('livewire.product.product-commission', [
            'products' => $products
        ])->layout('layouts.app');
    }
}
