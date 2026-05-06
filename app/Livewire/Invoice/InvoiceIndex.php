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
    public $editingInvoiceId = null;
    public $editCustomerId = null;
    public $editCustomerSearch = '';
    public $editProductSearch = '';
    public $editingItems = [];
    public $itemsToDelete = [];

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
            $this->editingInvoiceId = null;
            $this->editingItems = [];
            $this->itemsToDelete = [];
            $this->editCustomerSearch = '';
            $this->editProductSearch = '';
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
        if (!auth()->user()->hasPermission('invoice.cancel')) {
            $this->dispatch('notify', message: 'Bạn không có quyền hủy hóa đơn!', type: 'error');
            return;
        }

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
        if (!auth()->user()->hasPermission('invoice.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền thực hiện trả hàng!', type: 'error');
            return;
        }

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

    public function getCustomersProperty()
    {
        if (strlen($this->editCustomerSearch) < 2) return [];

        return \App\Models\Customer::query()
            ->where('full_name', 'like', "%{$this->editCustomerSearch}%")
            ->orWhere('phone', 'like', "%{$this->editCustomerSearch}%")
            ->orWhere('customer_code', 'like', "%{$this->editCustomerSearch}%")
            ->take(10)
            ->get();
    }

    public function selectEditCustomer($id, $name)
    {
        $this->editCustomerId = $id;
        $this->editCustomerSearch = $name;
    }

    public function getProductsProperty()
    {
        if (strlen($this->editProductSearch) < 2) return [];

        return \App\Models\Product::query()
            ->where('name', 'like', "%{$this->editProductSearch}%")
            ->orWhere('sku', 'like', "%{$this->editProductSearch}%")
            ->where('is_active', true)
            ->take(10)
            ->get();
    }

    public function addProductToEditing($productId)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) return;

        // Check if already in editing items
        foreach ($this->editingItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $this->editingItems[$index]['quantity']++;
                $this->editProductSearch = '';
                return;
            }
        }

        $this->editingItems[] = [
            'id' => null, // New item
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->sale_price,
            'quantity' => 1,
            'original_quantity' => 0,
        ];
        $this->editProductSearch = '';
    }

    public function removeItemFromEditing($index)
    {
        $item = $this->editingItems[$index];
        if ($item['id']) {
            $this->itemsToDelete[] = $item['id'];
        }
        
        unset($this->editingItems[$index]);
        $this->editingItems = array_values($this->editingItems);
    }

    public function editInvoice($id)
    {
        $this->editingInvoiceId = $id;
        $invoice = Invoice::with('items')->find($id);
        $this->editCustomerId = $invoice->customer_id;
        $this->editCustomerSearch = $invoice->customer?->full_name ?? 'Khách lẻ';
        $this->itemsToDelete = [];
        
        $this->editingItems = $invoice->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'sku' => $item->sku,
            'unit_price' => $item->unit_price,
            'quantity' => $item->quantity,
            'original_quantity' => $item->quantity,
        ])->toArray();
    }

    public function updateEditingQuantity($index, $delta)
    {
        $this->editingItems[$index]['quantity'] += $delta;
        if ($this->editingItems[$index]['quantity'] < 1) {
            $this->editingItems[$index]['quantity'] = 1;
        }
    }

    public function cancelEdit()
    {
        $this->editingInvoiceId = null;
        $this->editingItems = [];
        $this->editCustomerSearch = '';
    }

    public function updateInvoice()
    {
        if (!auth()->user()->hasPermission('invoice.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền sửa hóa đơn!', type: 'error');
            return;
        }

        $invoice = Invoice::findOrFail($this->editingInvoiceId);
        
        \DB::transaction(function () use ($invoice) {
            $totalAmount = 0;

            // Handle deletions
            foreach ($this->itemsToDelete as $itemId) {
                $item = \App\Models\InvoiceItem::find($itemId);
                if ($item) {
                    // Restore stock for deleted item
                    if ($item->product) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                    $item->delete();
                }
            }

            foreach ($this->editingItems as $itemData) {
                if ($itemData['id']) {
                    // Update existing item
                    $item = \App\Models\InvoiceItem::find($itemData['id']);
                    $diff = $itemData['quantity'] - $itemData['original_quantity'];

                    if ($diff != 0 && $item->product) {
                        $item->product->decrement('stock_quantity', $diff);
                    }

                    $finalPrice = $itemData['unit_price'] * $itemData['quantity'];
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'final_price' => $finalPrice,
                    ]);
                    $totalAmount += $finalPrice;
                } else {
                    // Create new item
                    $finalPrice = $itemData['unit_price'] * $itemData['quantity'];
                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $itemData['product_id'],
                        'sku' => $itemData['sku'],
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'final_price' => $finalPrice,
                    ]);
                    
                    // Subtract stock for new item
                    $product = \App\Models\Product::find($itemData['product_id']);
                    if ($product) {
                        $product->decrement('stock_quantity', $itemData['quantity']);
                    }
                    $totalAmount += $finalPrice;
                }
            }

            $invoice->update([
                'customer_id' => $this->editCustomerId,
                'total_amount' => $totalAmount,
                'final_amount' => max(0, $totalAmount - $invoice->discount_amount + $invoice->extra_fee),
            ]);
        });

        $this->editingInvoiceId = null;
        $this->editingItems = [];
        $this->itemsToDelete = [];
        $this->editCustomerSearch = '';
        $this->editProductSearch = '';
        
        $this->dispatch('notify', message: 'Hóa đơn đã được cập nhật thành công!', type: 'success');
    }

    public function getEditingTotalProperty()
    {
        return collect($this->editingItems)->sum(fn($item) => $item['unit_price'] * $item['quantity']);
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
