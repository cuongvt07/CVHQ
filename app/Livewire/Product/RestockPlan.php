<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class RestockPlan extends Component
{
    use WithPagination;

    public $threshold = 10;
    public $search = '';
    public $selectedCategories = [];
    public $brandFilter = '';
    public $boxCode = '';
    public $perPage = 25;

    protected $queryString = [
        'threshold' => ['except' => 10],
        'search' => ['except' => ''],
        'brandFilter' => ['except' => ''],
        'boxCode' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function updatingThreshold()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategories()
    {
        $this->resetPage();
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
        } elseif ($type === 'search') {
            $this->search = '';
        } elseif ($type === 'all') {
            $this->search = '';
            $this->selectedCategories = [];
            $this->brandFilter = '';
            $this->boxCode = '';
            $this->threshold = 10;
        }
        
        $this->resetPage();
    }

    public function getLowStockProducts()
    {
        return Product::query()
            ->where('stock_quantity', '<=', (int)($this->threshold ?: 0))
            ->when($this->search, function($query) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('sku', 'like', "%{$keyword}%")
                          ->orWhere('brand', 'like', "%{$keyword}%");
                    });
                }
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
            ->orderBy('stock_quantity', 'asc')
            ->paginate($this->perPage)
            ->onEachSide(1);
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
                        'Điều chỉnh thủ công (Kế hoạch nhập hàng)'
                    );
                }
            }

            $product->update([$field => $value]);
            $this->dispatch('notify', message: 'Cập nhật thành công!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
            $this->dispatch('$refresh');
        }
    }

    public function render()
    {
        return view('livewire.product.restock-plan', [
            'products' => $this->getLowStockProducts(),
            'categories_list' => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
            'brands_list' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
        ])->layout('layouts.app');
    }
}
