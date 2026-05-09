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

class ProductIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions;

    protected function getModuleKey(): string
    {
        return 'products';
    }

    public $search = '';
    public $category = 'All';
    public $selectedCategories = [];
    public $boxCode = '';
    public $brandFilter = '';
    public $stockStatus = 'all';
    public $importFile;
    public $perPage = 10;

    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => 'All'],
        'perPage' => ['except' => 10],
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

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Form properties
    public $productId;
    public $sku, $name, $category_path, $brand, $sale_price, $stock_quantity, $location;
    public $is_active = true;
    public $newImage;
    public $existingImage;

    protected $rules = [
        'sku' => 'required|unique:products,sku',
        'name' => 'required|min:3',
        'category_path' => 'nullable',
        'brand' => 'nullable',
        'sale_price' => 'required|numeric|min:0',
        'stock_quantity' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'newImage' => 'nullable|image|max:2048',
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
        $this->name = '';
        $this->category_path = '';
        $this->brand = '';
        $this->sale_price = 0;
        $this->stock_quantity = 999;
        $this->location = '';
        $this->is_active = true;
        $this->newImage = null;
        $this->existingImage = null;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-product-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->category_path = $product->category_path;
        $this->brand = $product->brand;
        $this->sale_price = $product->sale_price;
        $this->stock_quantity = $product->stock_quantity;
        $this->location = $product->location;
        $this->is_active = $product->is_active;
        $this->existingImage = !empty($product->images) ? $product->images[0] : null;
        
        $this->dispatch('open-product-modal');
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->productId) {
            $rules['sku'] = 'required|unique:products,sku,' . $this->productId;
        }

        $this->validate($rules);

        $data = [
            'sku' => $this->sku,
            'name' => $this->name,
            'category_path' => $this->category_path,
            'brand' => $this->brand,
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->stock_quantity === '' || $this->stock_quantity === null ? 999 : $this->stock_quantity,
            'location' => $this->location,
            'is_active' => $this->is_active,
        ];

        if ($this->newImage) {
            $path = $this->newImage->store('products', 'public');
            $data['images'] = [asset('storage/' . $path)];
        }

        if ($this->productId) {
            Product::find($this->productId)->update($data);
            $this->dispatch('notify', message: 'Cập nhật sản phẩm thành công!', type: 'success');
        } else {
            Product::create($data);
            $this->dispatch('notify', message: 'Thêm sản phẩm thành công!', type: 'success');
        }

        $this->dispatch('close-product-modal');
        $this->resetForm();
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

    public function getProducts()
    {
        return Product::query()
            ->when($this->search, function($query) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('sku', 'like', "%{$keyword}%")
                          ->orWhere('brand', 'like', "%{$keyword}%")
                          ->orWhere('location', 'like', "%{$keyword}%");
                    });
                }

                $query->orderByRaw("CASE 
                    WHEN sku = ? THEN 1 
                    WHEN sku LIKE ? THEN 2 
                    WHEN name LIKE ? THEN 3 
                    ELSE 4 
                END", [$this->search, $this->search . '%', $this->search . '%']);
            })
            ->when($this->selectedCategories, function($query) {
                $query->whereIn('category_path', $this->selectedCategories);
            })
            ->when($this->boxCode, function($query) {
                $query->where('location', 'like', "%{$this->boxCode}%");
            })
            ->when($this->brandFilter, function($query) {
                $query->where('brand', $this->brandFilter);
            })
            ->when($this->stockStatus !== 'all', function($query) {
                if ($this->stockStatus === 'in_stock') $query->where('stock_quantity', '>', 0);
                if ($this->stockStatus === 'out_of_stock') $query->where('stock_quantity', '<=', 0);
                if ($this->stockStatus === 'low_stock') $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
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
