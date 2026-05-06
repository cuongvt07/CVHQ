<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\InvoicesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\WithBulkActions;
use App\Traits\HasPermissions;

class InvoiceIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions;

    protected function getModuleKey(): string
    {
        return 'invoices';
    }
 
    public $search = '';
    public $importFile;
    public $perPage = 10;
    public $expandedInvoiceId = null;
    public $cancelReason = '';
    public $selectedInvoiceIdForCancel = null;
    public $showCancelModal = false;
    public $showEditModal = false;
    public $editingInvoice = null;
    public $editCustomerId = null;

    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function import()
    {
        // Tăng thời gian thực thi ngay từ đầu để tránh lỗi timeout trong quá trình validate và đọc file
        set_time_limit(300);

        $this->validate([
            'importFile' => 'required',
        ]);

        $this->importBatchId = Str::random(10);
        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            // Lưu file vào disk local (thư mục imports) để đảm bảo file tồn tại khi Queue worker xử lý
            $filePath = $this->importFile->store('imports', 'local');

            $import = new InvoicesImport();
            $import->setImportKey($this->importBatchId);
            Excel::import($import, $filePath, 'local');
            
            $this->importFile = null;
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->importing = false;
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $this->importErrors[] = "Dòng {$failure->row()}: " . implode(', ', $failure->errors());
            }
        } catch (\Exception $e) {
            $this->importing = false;
            $this->importErrors[] = $e->getMessage();
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
                    $this->dispatch('notify', message: 'Import hoàn tất thành công!', type: 'success');
                }
                
                $this->dispatch('import-finished', id: 'invoices');
            }
        }
    }

    public function getInvoices()
    {
        return Invoice::query()
            ->where(function($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'Cancelled');
            })
            ->when($this->search, fn($q) => $q->where(function($sub) {
                $sub->where('invoice_code', 'like', "%{$this->search}%")
                    ->orWhere('seller_name', 'like', "%{$this->search}%");
            }))
            ->with(['customer'])
            ->latest()
            ->paginate($this->perPage);
    }

    public function toggleDetails($id)
    {
        if ($this->expandedInvoiceId === $id) {
            $this->expandedInvoiceId = null;
        } else {
            $this->expandedInvoiceId = $id;
        }
    }

    public function confirmCancel($id)
    {
        $this->selectedInvoiceIdForCancel = $id;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function cancelInvoice()
    {
        $this->validate([
            'cancelReason' => 'required|min:5',
        ], [
            'cancelReason.required' => 'Vui lòng nhập lý do hủy.',
            'cancelReason.min' => 'Lý do hủy phải có ít nhất 5 ký tự.',
        ]);

        $invoice = Invoice::findOrFail($this->selectedInvoiceIdForCancel);
        $invoice->cancel($this->cancelReason, auth()->id());

        $this->showCancelModal = false;
        $this->selectedInvoiceIdForCancel = null;
        $this->cancelReason = '';

        $this->dispatch('notify', message: 'Hóa đơn đã được hủy và hàng đã được hoàn kho!', type: 'success');
    }

    public function returnItems($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status === 'Returned' || $invoice->status === 'Cancelled') {
            $this->dispatch('notify', message: 'Hóa đơn này không thể trả hàng thêm.', type: 'error');
            return;
        }

        \DB::transaction(function () use ($invoice) {
            // Restore stock
            foreach ($invoice->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $invoice->update([
                'status' => 'Returned',
            ]);
        });

        $this->dispatch('notify', message: 'Đã hoàn tất trả hàng và nhập lại kho!', type: 'success');
    }

    public function editInvoice($id)
    {
        $this->editingInvoice = Invoice::findOrFail($id);
        $this->editCustomerId = $this->editingInvoice->customer_id;
        $this->showEditModal = true;
    }

    public function updateInvoice()
    {
        $this->editingInvoice->update([
            'customer_id' => $this->editCustomerId,
        ]);

        $this->showEditModal = false;
        $this->editingInvoice = null;
        
        $this->dispatch('notify', message: 'Đã cập nhật thông tin hóa đơn!', type: 'success');
    }

    protected function getRecordsForBulk()
    {
        return $this->getInvoices();
    }

    protected function getModelForBulk()
    {
        return Invoice::class;
    }

    public function render()
    {
        return view('livewire.invoice.invoice-index', [
            'invoices' => $this->getInvoices()
        ])->layout('layouts.app');
    }
}
