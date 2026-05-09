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
    public $perPage = 25;

    protected $queryString = [
        'threshold' => ['except' => 10],
        'search' => ['except' => ''],
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
        } elseif ($type === 'search') {
            $this->search = '';
        } elseif ($type === 'all') {
            $this->search = '';
            $this->selectedCategories = [];
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
            ->orderBy('stock_quantity', 'asc')
            ->paginate($this->perPage)
            ->onEachSide(1);
    }

    public function render()
    {
        return view('livewire.product.restock-plan', [
            'products' => $this->getLowStockProducts(),
            'categories_list' => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
        ])->layout('layouts.app');
    }
}
