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
use App\Traits\WithUserPreferences;
use App\Traits\WithColumnVisibility;

class InvoiceIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences;

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
    public $editSalesChannel = '';
    public $editPaymentMethod = 'cash';

    protected function getDefaultVisibleColumns(): array
    {
        return ['code', 'time', 'customer', 'amount', 'channel', 'method', 'status', 'actions'];
    }


    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    public $startDate = '';
    public $endDate = '';
    public $sellerFilter = '';
    public $statusFilter = 'active';
    public $paymentMethodFilter = '';
    public $salesChannelFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'sellerFilter' => ['except' => ''],
        'statusFilter' => ['except' => 'active'],
        'paymentMethodFilter' => ['except' => ''],
        'salesChannelFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'visibleColumns' => ['except' => ['code', 'customer', 'amount', 'channel', 'method', 'status', 'date']],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethodFilter()
    {
        $this->resetPage();
    }

    public function updatingSalesChannelFilter()
    {
        $this->resetPage();
    }

    /**
     * Quick-select date range presets for the date filter.
     * Keys: today, yesterday, this_week, this_month, last_month.
     */
    public function setDatePreset(string $key): void
    {
        $now = \Carbon\Carbon::now();
        switch ($key) {
            case 'today':
                $this->startDate = $now->copy()->startOfDay()->toDateString();
                $this->endDate   = $now->copy()->endOfDay()->toDateString();
                break;
            case 'yesterday':
                $this->startDate = $now->copy()->subDay()->startOfDay()->toDateString();
                $this->endDate   = $now->copy()->subDay()->endOfDay()->toDateString();
                break;
            case 'this_week':
                $this->startDate = $now->copy()->startOfWeek()->toDateString();
                $this->endDate   = $now->copy()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $this->startDate = $now->copy()->startOfMonth()->toDateString();
                $this->endDate   = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $this->endDate   = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'all':
                $this->startDate = '';
                $this->endDate = '';
                break;
        }
        $this->resetPage();
    }

    public function clearFilter($key)
    {
        switch ($key) {
            case 'search':
                $this->search = '';
                break;
            case 'startDate':
                $this->startDate = '';
                break;
            case 'endDate':
                $this->endDate = '';
                break;
            case 'sellerFilter':
                $this->sellerFilter = '';
                break;
            case 'statusFilter':
                $this->statusFilter = 'active';
                break;
            case 'paymentMethodFilter':
                $this->paymentMethodFilter = '';
                break;
            case 'salesChannelFilter':
                $this->salesChannelFilter = '';
                break;
            case 'all':
                $this->search = '';
                $this->startDate = '';
                $this->endDate = '';
                $this->sellerFilter = '';
                $this->statusFilter = 'active';
                $this->paymentMethodFilter = '';
                $this->salesChannelFilter = '';
                break;
        }
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
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'active') {
                    // "Đang hoạt động" = Completed/null, i.e. NOT Cancelled and NOT Returned.
                    // NULL must remain visible (legacy/imported invoices).
                    $q->where(function ($sub) {
                        $sub->whereNull('status')
                            ->orWhereNotIn('status', ['Cancelled', 'Returned']);
                    });
                } elseif ($this->statusFilter === 'cancelled') {
                    $q->where('status', 'Cancelled');
                } elseif ($this->statusFilter === 'returned') {
                    $q->where('status', 'Returned');
                }
            })
            ->when($this->search, fn($q) => $q->where(function($sub) {
                $sub->where('invoice_code', 'like', "%{$this->search}%")
                    ->orWhere('seller_name', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function ($cq) {
                        $cq->where('full_name', 'like', "%{$this->search}%")
                           ->orWhere('phone', 'like', "%{$this->search}%")
                           ->orWhere('customer_code', 'like', "%{$this->search}%");
                    });
            }))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->sellerFilter, fn($q) => $q->where('seller_name', 'like', "%{$this->sellerFilter}%"))
            ->when($this->paymentMethodFilter, function ($q) {
                match ($this->paymentMethodFilter) {
                    'cash' => $q->where('cash_amount', '>', 0),
                    'transfer' => $q->where('transfer_amount', '>', 0),
                    'card' => $q->where('card_amount', '>', 0),
                    'wallet' => $q->where('wallet_amount', '>', 0),
                    default => null,
                };
            })
            ->when($this->salesChannelFilter, fn($q) => $q->where('sales_channel', $this->salesChannelFilter))
            ->with(['customer'])
            ->latest()
            ->paginate($this->perPage)
            ->onEachSide(1);
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
        if (!auth()->user()->hasPermission('invoice.cancel')) {
            $this->dispatch('notify', message: 'Bạn không có quyền hủy hóa đơn!', type: 'error');
            return;
        }

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
        if (!auth()->user()->hasPermission('invoice.return')) {
            $this->dispatch('notify', message: 'Bạn không có quyền thực hiện trả hàng!', type: 'error');
            return;
        }

        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status === 'Returned' || $invoice->status === 'Cancelled') {
            $this->dispatch('notify', message: 'Hóa đơn này không thể trả hàng thêm.', type: 'error');
            return;
        }

        $restored = 0;
        $skipped = 0;

        \DB::transaction(function () use ($invoice, &$restored, &$skipped) {
            // Hoàn (cộng bù) tồn kho cho từng sản phẩm.
            // Dùng withTrashed + fallback theo SKU để không bỏ sót sản phẩm
            // đã xoá mềm hoặc hoá đơn nhập khẩu không gắn product_id.
            foreach ($invoice->items as $item) {
                $qty = (int) $item->quantity;
                if ($qty <= 0) {
                    continue;
                }

                $product = $item->product()->withTrashed()->first();
                if (!$product && $item->sku) {
                    $product = \App\Models\Product::withTrashed()->where('sku', $item->sku)->first();
                }

                if (!$product) {
                    $skipped++;
                    continue;
                }

                // Ghi thẻ kho trước (lấy tồn trước khi cộng), rồi cộng bù tồn kho.
                $product->recordStockHistory(
                    'Return',
                    $qty,
                    $invoice->id,
                    $invoice->invoice_code,
                    'Trả hàng'
                );
                $product->increment('stock_quantity', $qty);
                $restored++;
            }

            $newCode = str_replace('HD', 'TH', $invoice->invoice_code);
            if (!str_contains($newCode, 'TH')) {
                $newCode = 'TH-' . $invoice->invoice_code;
            }

            $invoice->update([
                'status' => 'Returned',
                'invoice_code' => $newCode,
                'cancelled_at' => now(), // Reusing cancelled_at or adding logic
            ]);
        });

        if ($restored === 0) {
            $this->dispatch('notify', message: 'Đã trả hàng nhưng KHÔNG cộng được tồn kho (hóa đơn không có dòng hàng hợp lệ / không khớp sản phẩm).', type: 'warning');
        } elseif ($skipped > 0) {
            $this->dispatch('notify', message: "Đã trả hàng và cộng bù tồn cho {$restored} sản phẩm; {$skipped} dòng không khớp sản phẩm.", type: 'warning');
        } else {
            $this->dispatch('notify', message: "Đã hoàn tất trả hàng và cộng bù tồn kho cho {$restored} sản phẩm!", type: 'success');
        }
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
            'commission_amount' => $product->commission_amount,
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
        if (!auth()->user()->hasPermission('invoice.edit')) {
            $this->dispatch('notify', message: 'Bạn không có quyền sửa hóa đơn!', type: 'error');
            return;
        }

        $this->editingInvoiceId = $id;
        $invoice = Invoice::with('items')->find($id);
        $this->editCustomerId = $invoice->customer_id;
        $this->editCustomerSearch = $invoice->customer?->full_name ?? 'Khách lẻ';
        $this->editSalesChannel = $invoice->sales_channel ?? '';
        $this->editPaymentMethod = $invoice->getPaymentMethodKey();
        $this->itemsToDelete = [];
        
        $this->editingItems = $invoice->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'sku' => $item->sku,
            'unit_price' => $item->unit_price,
            'commission_amount' => $item->commission_amount,
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
        $this->editSalesChannel = '';
        $this->editPaymentMethod = 'cash';
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
            $seller = $invoice->user;
            $canReceiveCommission = $seller ? $seller->can_receive_commission : true;
            $totalCommission = 0;

            // Handle deletions
            foreach ($this->itemsToDelete as $itemId) {
                $item = \App\Models\InvoiceItem::find($itemId);
                if ($item) {
                    // Restore stock for deleted item + ghi thẻ kho
                    if ($item->product && (int) $item->quantity !== 0) {
                        $before = (int) $item->product->stock_quantity;
                        $item->product->increment('stock_quantity', $item->quantity);
                        $item->product->recordStockHistory(
                            'Adjustment', (int) $item->quantity,
                            $invoice->id, $invoice->invoice_code,
                            'Bỏ sản phẩm khỏi hóa đơn (sửa HĐ)', $before
                        );
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
                        $before = (int) $item->product->stock_quantity;
                        $item->product->decrement('stock_quantity', $diff);
                        $item->product->recordStockHistory(
                            'Adjustment', -(int) $diff,
                            $invoice->id, $invoice->invoice_code,
                            'Sửa số lượng sản phẩm trên hóa đơn', $before
                        );
                    }

                    $finalPrice = $itemData['unit_price'] * $itemData['quantity'];
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'final_price' => $finalPrice,
                    ]);
                    $totalAmount += $finalPrice;
                    $totalCommission += ($item->commission_amount * $itemData['quantity']);
                } else {
                    // Create new item
                    $finalPrice = $itemData['unit_price'] * $itemData['quantity'];
                    $commissionPerUnit = $canReceiveCommission ? ($itemData['commission_amount'] ?? 0) : 0;

                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $itemData['product_id'],
                        'sku' => $itemData['sku'],
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'commission_amount' => $commissionPerUnit,
                        'final_price' => $finalPrice,
                    ]);
                    
                    // Subtract stock for new item + ghi thẻ kho
                    $product = \App\Models\Product::find($itemData['product_id']);
                    if ($product && (int) $itemData['quantity'] !== 0) {
                        $before = (int) $product->stock_quantity;
                        $product->decrement('stock_quantity', $itemData['quantity']);
                        $product->recordStockHistory(
                            'Adjustment', -(int) $itemData['quantity'],
                            $invoice->id, $invoice->invoice_code,
                            'Thêm sản phẩm vào hóa đơn (sửa HĐ)', $before
                        );
                    }
                    $totalAmount += $finalPrice;
                    $totalCommission += ($commissionPerUnit * $itemData['quantity']);
                }
            }

            // Allocate payment into the matching column
            $finalAmount = max(0, $totalAmount - $invoice->discount_amount + $invoice->extra_fee);
            $paymentColumns = [
                'cash_amount' => 0,
                'transfer_amount' => 0,
                'card_amount' => 0,
                'wallet_amount' => 0,
            ];
            $paymentColumns[$this->editPaymentMethod . '_amount'] = $finalAmount;

            $invoice->update(array_merge([
                'customer_id' => $this->editCustomerId,
                'sales_channel' => $this->editSalesChannel ?: null,
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'final_amount' => $finalAmount,
            ], $paymentColumns));
        });

        $this->editingInvoiceId = null;
        $this->editingItems = [];
        $this->itemsToDelete = [];
        $this->editCustomerSearch = '';
        $this->editProductSearch = '';
        $this->editSalesChannel = '';
        $this->editPaymentMethod = 'cash';
        
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
