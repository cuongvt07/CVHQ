<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\WithBulkActions;
use App\Traits\HasPermissions;
use App\Traits\WithUserPreferences;

class ProductIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'products';
    }

    protected function getPersistedProperties(): array
    {
        return ['perPage', 'branch'];
    }

    public $search = '';
    public $category = 'All';
    public $selectedCategories = [];
    public $boxCode = '';
    public $brandFilter = '';
    public $stockStatus = 'all';
    public $importFile;
    public $perPage = 10;
    public $branch = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $visibleColumns = [];

    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    // Commission Settings
    public $showCommissionSettings = false;
    public $autoCommissionEnabled = false;
    public $commissionRanges = [];

    protected $queryString = [
        'perPage' => ['except' => 10],
        'branch' => ['except' => 'all'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'visibleColumns' => ['except' => ['sku', 'name', 'brand', 'category', 'price', 'stock', 'location', 'actions']],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingCategory()
    {
        $this->resetPage();
        if ($this->category !== 'All') {
            $this->selectedCategories = [$this->category];
        } else {
            $this->selectedCategories = [];
        }
    }

    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatedBoxCode()
    {
        $this->resetPage();
    }

    public function updatedStockStatus()
    {
        $this->resetPage();
    }

    public function updatedBranch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedSalePrice()
    {
        if (\App\Models\SystemSetting::get('auto_commission_enabled') !== 'true') {
            return;
        }

        $price = (int)$this->sale_price;
        $ranges = \App\Models\SystemSetting::get('commission_ranges', []);
        
        foreach ($ranges as $range) {
            if ($price >= $range['min'] && $price < $range['max']) {
                $this->commission_amount = $range['amount'];
                break;
            }
        }
    }

    public function clearFilter($type, $value = null)
    {
        if ($type === 'selectedCategories') {
            if ($value) {
                $this->selectedCategories = array_diff($this->selectedCategories, [$value]);
            } else {
                $this->selectedCategories = [];
            }
        } elseif ($type === 'boxCode') {
            $this->boxCode = '';
        } elseif ($type === 'brandFilter') {
            $this->brandFilter = '';
        } elseif ($type === 'stockStatus') {
            $this->stockStatus = 'all';
        } elseif ($type === 'search') {
            $this->search = '';
        }
        
        $this->resetPage();
    }

    public $expandedProductId = null;

    public function toggleHistory($id)
    {
        if ($this->expandedProductId === $id) {
            $this->expandedProductId = null;
        } else {
            $this->expandedProductId = $id;
        }
    }

    // Form properties
    public $productId;
    public $sku, $base_name, $category_path, $brand, $sale_price, $commission_amount, $stock_quantity, $location;
    public $is_active = true;
    public $newImages = []; // Array of UploadedFile objects
    public $existingImages = []; // Array of strings (paths)
    public $capturedImages = []; // Array of Base64 strings
    public $productAttributes = []; // [['key' => '', 'value' => '']]
    public $existingKeys = [];

    protected $rules = [
        'sku' => 'required|unique:products,sku',
        'base_name' => 'required|min:3',
        'category_path' => 'nullable',
        'brand' => 'nullable',
        'sale_price' => 'required|numeric|min:0',
        'stock_quantity' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'newImages.*' => 'nullable|image|max:5120',
    ];

    public function import()
    {
        // Tăng thời gian thực thi ngay từ đầu để tránh lỗi timeout trong quá trình validate và đọc file
        set_time_limit(300);

        $this->validate([
            'importFile' => 'required', // Chỉ yêu cầu có file, bỏ qua mimes/max để tránh lỗi metadata trên server
        ]);

        $this->importBatchId = Str::random(10);
        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            $import = new ProductsImport();
            $import->setImportKey($this->importBatchId);
            
            // Store the file to ensure it's available for the queue worker
            $filePath = $this->importFile->store('imports');
            
            Excel::queueImport($import, $filePath);
            
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
                
                $this->dispatch('import-finished', id: 'products');
            }
        }
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->sku = '';
        $this->base_name = '';
        $this->category_path = '';
        $this->brand = '';
        $this->sale_price = 0;
        $this->commission_amount = 0;
        $this->stock_quantity = 999;
        $this->location = '';
        $this->is_active = true;
        $this->newImages = [];
        $this->existingImages = [];
        $this->capturedImages = [];
        $this->productAttributes = [];
        $this->resetErrorBag();
    }

    public function addAttribute()
    {
        $this->productAttributes[] = ['key' => '', 'value' => ''];
    }

    public function removeAttribute($index)
    {
        unset($this->productAttributes[$index]);
        $this->productAttributes = array_values($this->productAttributes);
    }

    public function loadAttributeSuggestions()
    {
        $this->existingKeys = Product::getUniqueAttributeKeys();
    }

    public function create()
    {
        $this->resetForm();
        $this->loadAttributeSuggestions();
        
        // Auto commission logic
        if (\App\Models\SystemSetting::get('auto_commission_enabled') === 'true') {
            $this->updatedSalePrice();
        }

        $this->dispatch('open-product-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->loadAttributeSuggestions();
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->sku = $product->sku;
        $this->base_name = $product->base_name;
        $this->category_path = $product->category_path;
        $this->brand = $product->brand;
        $this->sale_price = $product->sale_price;
        $this->commission_amount = $product->commission_amount;
        $this->stock_quantity = $product->stock_quantity;
        $this->location = $product->location;
        $this->is_active = $product->is_active;
        $this->existingImages = is_array($product->images) ? $product->images : [];
        $this->newImages = [];
        $this->capturedImages = [];
        
        $this->productAttributes = [];
        if (!empty($product->attributes) && is_array($product->attributes)) {
            foreach ($product->attributes as $key => $value) {
                $this->productAttributes[] = ['key' => $key, 'value' => $value];
            }
        }

        $this->dispatch('open-product-modal');
    }

    public function save($keepOpen = false)
    {
        $currentSku = $this->sku;
        $rules = $this->rules;
        if ($this->productId) {
            $rules['sku'] = 'required|unique:products,sku,' . $this->productId;
        }

        $this->validate($rules);

        // Parse structured attributes
        $attributes = [];
        foreach ($this->productAttributes as $attr) {
            $key = trim($attr['key'] ?? '');
            if (!empty($key)) {
                $attributes[$key] = trim($attr['value'] ?? '');
            }
        }

        $productData = [
            'sku' => $this->sku,
            'base_name' => $this->base_name,
            'category_path' => $this->category_path,
            'brand' => $this->brand,
            'sale_price' => $this->sale_price,
            'commission_amount' => $this->commission_amount,
            'stock_quantity' => $this->stock_quantity === '' || $this->stock_quantity === null ? 999 : $this->stock_quantity,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'attributes' => $attributes,
        ];

        // Process Images
        $allImagePaths = $this->existingImages;

        // Handle uploaded images
        foreach ($this->newImages as $image) {
            $allImagePaths[] = $image->store('products', 'public');
        }

        // Handle captured images (Base64)
        foreach ($this->capturedImages as $base64) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
            $fileName = 'products/' . Str::random(40) . '.png';
            \Storage::disk('public')->put($fileName, $imageData);
            $allImagePaths[] = $fileName;
        }

        $productData['images'] = $allImagePaths;

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($productData);
            $this->dispatch('notify', message: 'Cập nhật sản phẩm thành công!', type: 'success');
        } else {
            Product::create($productData);
            $this->dispatch('notify', message: 'Thêm sản phẩm thành công!', type: 'success');
        }

        if ($keepOpen) {
            $nextSku = $this->getNextSku($currentSku);
            
            // Clone data logic: keep everything except specific fields
            $this->productId = null;
            $this->sku = $nextSku;
            $this->location = '';
            $this->productAttributes = [];
            
            $this->loadAttributeSuggestions();
            $this->dispatch('notify', message: 'Đã lưu và sao chép dữ liệu cho sản phẩm tiếp theo!', type: 'success');
        } else {
            $this->dispatch('close-product-modal');
            $this->resetForm();
        }
    }

    public function saveAndCreateNext()
    {
        $this->save(true);
    }

    // Commission Settings Methods
    public function openCommissionSettings()
    {
        $this->autoCommissionEnabled = \App\Models\SystemSetting::get('auto_commission_enabled') === 'true';
        $this->commissionRanges = \App\Models\SystemSetting::get('commission_ranges', []);
        $this->showCommissionSettings = true;
        $this->dispatch('open-commission-modal');
    }

    public function addCommissionRange()
    {
        $this->commissionRanges[] = ['min' => 0, 'max' => 0, 'amount' => 0];
    }

    public function removeCommissionRange($index)
    {
        unset($this->commissionRanges[$index]);
        $this->commissionRanges = array_values($this->commissionRanges);
    }

    public function saveCommissionSettings()
    {
        \App\Models\SystemSetting::set('auto_commission_enabled', $this->autoCommissionEnabled ? 'true' : 'false');
        \App\Models\SystemSetting::set('commission_ranges', $this->commissionRanges);
        
        $this->showCommissionSettings = false;
        $this->dispatch('close-commission-modal');
        $this->dispatch('notify', message: 'Đã lưu cấu hình hoa hồng!', type: 'success');
    }

    protected function getDefaultVisibleColumns(): array
    {
        return ['sku', 'name', 'brand', 'category', 'price', 'stock', 'location', 'actions'];
    }

    public function bulkCopyToSG()
    {
        if (empty($this->selectedRows)) {
            $this->dispatch('notify', message: 'Vui lòng chọn sản phẩm cần sao chép!', type: 'warning');
            return;
        }

        $products = Product::whereIn('id', $this->selectedRows)->get();
        $count = 0;
        $skipped = 0;

        foreach ($products as $product) {
            // Skip if already a SG product
            if (Str::startsWith($product->sku, 'Z')) {
                $skipped++;
                continue;
            }

            $newSku = 'Z' . $product->sku;

            // Check if SKU already exists
            if (Product::where('sku', $newSku)->exists()) {
                $skipped++;
                continue;
            }

            // Create new SG product
            $newProduct = $product->replicate();
            $newProduct->sku = $newSku;
            $newProduct->stock_quantity = 0;
            $newProduct->location = '';
            $newProduct->save();
            
            $count++;
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        
        $message = "Đã sao chép thành công {$count} sản phẩm sang SG.";
        if ($skipped > 0) {
            $message .= " (Bỏ qua {$skipped} mã đã tồn tại hoặc không hợp lệ)";
        }

        $this->dispatch('notify', message: $message, type: 'success');
        $this->branch = 'sg'; // Switch to SG branch to see new items
    }

    public function removeImage($index, $type)
    {
        if ($type === 'existing') {
            unset($this->existingImages[$index]);
            $this->existingImages = array_values($this->existingImages);
        } elseif ($type === 'new') {
            unset($this->newImages[$index]);
            $this->newImages = array_values($this->newImages);
        } elseif ($type === 'captured') {
            unset($this->capturedImages[$index]);
            $this->capturedImages = array_values($this->capturedImages);
        }
    }

    public function addCapturedImage($dataUri)
    {
        $this->capturedImages[] = $dataUri;
    }

    private function getNextSku($sku)
    {
        if (empty($sku)) return '';

        $nextSku = $sku;
        $iteration = 0;

        while ($iteration < 100) { // Safety break
            // Try to match pattern TEXT-NUMBER or just TEXTNUMBER
            if (preg_match('/^(.*?)(\d+)$/', $nextSku, $matches)) {
                $prefix = $matches[1];
                $number = (int)$matches[2];
                $nextNumber = $number + 1;
                
                // Keep leading zeros if any
                $nextNumberStr = str_pad((string)$nextNumber, strlen($matches[2]), '0', STR_PAD_LEFT);
                $nextSku = $prefix . $nextNumberStr;
            } else {
                // If no trailing number, just add -1
                $nextSku = $nextSku . '-1';
            }

            // Check if this SKU exists
            if (!Product::where('sku', $nextSku)->exists()) {
                return $nextSku;
            }
            
            $iteration++;
        }

        return $nextSku . '-copy';
    }

    public function confirmDelete($id)
    {
        $this->productId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        Product::find($this->productId)->delete();
        $this->dispatch('notify', message: 'Đã xóa sản phẩm!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->productId = null;
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        $this->dispatch('notify', message: 'Cập nhật trạng thái thành công!', type: 'success');
    }

    public function updateField($id, $field, $value)
    {
        $product = Product::findOrFail($id);
        
        $rules = [
            'location' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|numeric|min:0',
        ];
        
        if (!isset($rules[$field])) return;

        try {
            $validator = \Validator::make([$field => $value], [$field => $rules[$field]]);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            if ($field === 'stock_quantity') {
                $change = (int)$value - (int)$product->stock_quantity;
                if ($change !== 0) {
                    $product->recordStockHistory(
                        'Adjustment', 
                        $change, 
                        null, 
                        null, 
                        'Điều chỉnh thủ công'
                    );
                }
            }

            $product->update([$field => $value]);
            $this->dispatch('notify', message: 'Cập nhật thành công!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
            // Force refresh to revert invalid UI state
            $this->dispatch('$refresh');
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getProducts()
    {
        $query = Product::query()
            ->when($this->branch !== 'all', function($query) {
                if ($this->branch === 'sg') {
                    $query->where('sku', 'LIKE', 'Z%');
                } else {
                    $query->where('sku', 'NOT LIKE', 'Z%');
                }
            })
            ->when($this->search, function($query) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->whereRaw("sku REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                          ->orWhereRaw("location REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                          ->orWhereRaw("name REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                          ->orWhereRaw("brand REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)']);
                    });
                }

                $query->orderByRaw("CASE 
                    WHEN sku = ? THEN 1 
                    WHEN location = ? THEN 2
                    WHEN sku LIKE ? THEN 3 
                    WHEN name LIKE ? THEN 4 
                    ELSE 5 
                END", [$this->search, $this->search, $this->search . '%', $this->search . '%']);
            })
            ->when($this->selectedCategories, function($query) {
                $query->whereIn('category_path', $this->selectedCategories);
            })
            ->when($this->boxCode, function($query) {
                $query->whereRaw("location REGEXP ?", ['(^|[^0-9])' . $this->boxCode . '([^0-9]|$)']);
            })
            ->when($this->brandFilter, function($query) {
                $query->where('brand', $this->brandFilter);
            })
            ->when($this->stockStatus !== 'all', function($query) {
                if ($this->stockStatus === 'in_stock') $query->where('stock_quantity', '>', 0);
                if ($this->stockStatus === 'out_of_stock') $query->where('stock_quantity', '<=', 0);
                if ($this->stockStatus === 'low_stock') $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            });

        if (!$this->search) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage)->onEachSide(1);
    }

    protected function getRecordsForBulk()
    {
        return $this->getProducts();
    }

    protected function getModelForBulk()
    {
        return Product::class;
    }

    public function render()
    {
        return view('livewire.product.product-index', [
            'products' => $this->getProducts(),
            'categories_list' => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
            'brands_list' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'box_codes_list' => Product::whereNotNull('location')->distinct()->pluck('location'),
        ])->layout('layouts.app');
    }
}
