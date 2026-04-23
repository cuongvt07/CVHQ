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

class ProductIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions;

    public $search = '';
    public $category = 'All';
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
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Form properties
    public $productId;
    public $sku, $name, $category_path, $brand, $sale_price, $stock_quantity;
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
        'is_active' => 'boolean',
        'newImage' => 'nullable|image|max:2048',
    ];

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
            $import = new ProductsImport();
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
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('sku', 'like', "%{$this->search}%")
                                              ->orWhere('brand', 'like', "%{$this->search}%"))
            ->when($this->category !== 'All', fn($q) => $q->where('category_path', $this->category))
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
            'products' => $this->getProducts()
        ])->layout('layouts.app');
    }
}
