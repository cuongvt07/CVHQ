<?php

namespace App\Livewire\Product;

use App\Models\Product;
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
            // Lưu file upload
            $filePath = $this->importFile->store('imports');
            $fullPath = storage_path('app/' . $filePath);

            // Chuẩn hóa file Excel (fix định dạng KiotViet không chuẩn)
            $normalizedPath = $this->normalizeExcelFile($fullPath);

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

            // Dọn file tạm
            @unlink($fullPath);
            if ($normalizedPath !== $fullPath) {
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
