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
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->importBatchId = Str::random(10);
        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            $import = new InvoicesImport();
            $import->setImportKey($this->importBatchId);
            Excel::import($import, $this->importFile->getRealPath());
            
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
        // For now, just a placeholder as requested
        $this->dispatch('notify', message: 'Tính năng trả hàng đang được phát triển.', type: 'info');
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
