<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasPermissions;

class PosTerminal extends Component
{
    use HasPermissions, WithPagination;

    protected function getModuleKey(): string
    {
        return 'pos';
    }
    public $cart = [];
    public $search = '';
    public $category = 'All';
    public $selectedCategories = [];
    public $boxCode = '';
    public $brandFilter = '';
    public $stockStatus = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleCategory($cat)
    {
        $this->resetPage();
        if ($cat === 'All') {
            $this->category = 'All';
            $this->selectedCategories = [];
            return;
        }

        $this->category = 'Custom';
        if (in_array($cat, $this->selectedCategories)) {
            $this->selectedCategories = array_diff($this->selectedCategories, [$cat]);
        } else {
            $this->selectedCategories[] = $cat;
        }

        if (empty($this->selectedCategories)) {
            $this->category = 'All';
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

    public function clearFilter($type, $value = null)
    {
        if ($type === 'selectedCategories') {
            if ($value) {
                $this->selectedCategories = array_diff($this->selectedCategories, [$value]);
            } else {
                $this->selectedCategories = [];
            }
            if (empty($this->selectedCategories)) {
                $this->category = 'All';
            }
        } elseif ($type === 'boxCode') {
            $this->boxCode = '';
        } elseif ($type === 'brandFilter') {
            $this->brandFilter = '';
        } elseif ($type === 'stockStatus') {
            $this->stockStatus = 'all';
        } elseif ($type === 'search') {
            $this->search = '';
        } elseif ($type === 'all') {
            $this->selectedCategories = [];
            $this->boxCode = '';
            $this->brandFilter = '';
            $this->stockStatus = 'all';
            $this->search = '';
            $this->category = 'All';
        }
        
        $this->resetPage();
    }

    // Financials
    public $discount = 0;
    public $extra_fee = 0;
    public $paid_amount = 0;

    // Customer Management
    public $customer_id = null;
    public $customer_search = '';
    public $show_customer_search = false;
    public $is_creating_customer = false;
    public $new_customer = [
        'full_name' => '',
        'phone' => ''
    ];

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if ($product) {
            $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] === $productId);
            
            if ($existingIndex !== false) {
                $this->cart[$existingIndex]['quantity']++;
            } else {
                $this->cart[] = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'sale_price' => (int) $product->sale_price,
                    'commission_amount' => (int) $product->commission_amount,
                    'image' => !empty($product->images) ? $product->images[0] : null,
                    'quantity' => 1
                ];
            }

            $this->dispatch('notify', 
                message: 'Đã thêm "' . $product->name . '" vào giỏ hàng', 
                type: 'success'
            );
        }
    }

    public function updateQuantity($productId, $delta)
    {
        $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] === $productId);
        
        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity'] += $delta;
            
            if ($this->cart[$existingIndex]['quantity'] <= 0) {
                $this->removeFromCart($productId);
            } else {
                $this->recalculateTotalDiscount();
            }
        }
    }

    public function applyItemDiscount($productId, $discountPerUnit)
    {
        $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] === $productId);
        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['item_discount'] = (int)$discountPerUnit;
            $this->recalculateTotalDiscount();
        }
    }

    public function removeFromCart($productId)
    {
        $this->cart = collect($this->cart)
            ->filter(fn($item) => $item['id'] !== $productId)
            ->values()
            ->toArray();
            
        $this->recalculateTotalDiscount();
        $this->dispatch('notify', message: 'Đã xóa khỏi giỏ hàng', type: 'warning');
    }

    protected function recalculateTotalDiscount()
    {
        $this->discount = collect($this->cart)->sum(function($item) {
            return ($item['item_discount'] ?? 0) * $item['quantity'];
        });
    }

    public function getProducts()
    {
        return Product::query()
            ->when($this->search, function($query) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('base_name', 'like', "%{$keyword}%")
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
                if ($this->stockStatus === 'in_stock') {
                    $query->where('stock_quantity', '>', 0);
                } elseif ($this->stockStatus === 'low_stock') {
                    $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
                } elseif ($this->stockStatus === 'out_of_stock') {
                    $query->where('stock_quantity', '<=', 0);
                }
            })
            ->where('is_active', true)
            ->orderBy('sku', 'asc')
            ->paginate(24)
            ->onEachSide(1)
            ->through(function($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'base_name' => $product->base_name,
                    'category_path' => $product->category_path,
                    'sale_price' => (int) $product->sale_price,
                    'location' => $product->location,
                    'stock_quantity' => $product->stock_quantity,
                    'image' => !empty($product->images) ? $product->images[0] : null,
                ];
            });
    }

    public function getCustomersProperty()
    {
        if (strlen($this->customer_search) < 2) return [];

        return \App\Models\Customer::query()
            ->where('full_name', 'like', "%{$this->customer_search}%")
            ->orWhere('phone', 'like', "%{$this->customer_search}%")
            ->orWhere('customer_code', 'like', "%{$this->customer_search}%")
            ->latest()
            ->take(5)
            ->get();
    }

    public function selectCustomer($id)
    {
        $this->customer_id = $id;
        $this->customer_search = '';
        $this->show_customer_search = false;
    }

    public function createCustomer()
    {
        $this->validate([
            'new_customer.full_name' => 'required|min:2',
            'new_customer.phone' => 'nullable|digits_between:10,11',
        ]);

        $customer = \App\Models\Customer::create([
            'full_name' => $this->new_customer['full_name'],
            'phone' => $this->new_customer['phone'],
            'customer_code' => 'KH' . time(),
            'status' => 'Active'
        ]);

        $this->customer_id = $customer->id;
        $this->is_creating_customer = false;
        $this->new_customer = ['full_name' => '', 'phone' => ''];
        
        $this->dispatch('notify', message: 'Tạo khách hàng mới thành công!', type: 'success');
    }

    public function getSelectedCustomerProperty()
    {
        return $this->customer_id ? \App\Models\Customer::find($this->customer_id) : null;
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['sale_price'] * $item['quantity']);
    }

    public function getFinalAmountProperty()
    {
        return max(0, $this->total - (int)$this->discount + (int)$this->extra_fee);
    }

    public function getChangeAmountProperty()
    {
        if (!$this->paid_amount) return 0;
        return max(0, (int)$this->paid_amount - $this->final_amount);
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Giỏ hàng đang trống!', type: 'error');
            return;
        }



        \DB::beginTransaction();
        try {
            $canReceiveCommission = auth()->user()->can_receive_commission;
            $totalCommission = $canReceiveCommission 
                ? collect($this->cart)->sum(fn($item) => $item['commission_amount'] * $item['quantity'])
                : 0;

            $invoice = \App\Models\Invoice::create([
                'invoice_code' => 'HD' . time(),
                'branch' => 'Antigravity HQ',
                'customer_id' => $this->customer_id,
                'user_id' => auth()->id(),
                'seller_name' => auth()->user()?->name ?? 'Admin POS',
                'sales_channel' => 'POS',
                'total_amount' => $this->total,
                'discount_amount' => $this->discount,
                'extra_fee' => $this->extra_fee,
                'final_amount' => $this->finalAmount,
                'total_commission' => $totalCommission,
                'paid_amount' => $this->finalAmount,
                'status' => 'Completed',
                'delivery_status' => 'Delivered'
            ]);

            foreach ($this->cart as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['id'],
                    'sku' => $item['sku'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['sale_price'],
                    'commission_amount' => $canReceiveCommission ? $item['commission_amount'] : 0,
                    'final_price' => $item['sale_price'] * $item['quantity']
                ]);

                // Trừ tồn kho sản phẩm
                $product = \App\Models\Product::find($item['id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            \DB::commit();

            // Clear cart and reset
            $this->cart = [];
            $this->discount = 0;
            $this->extra_fee = 0;
            $this->paid_amount = 0;
            $this->customer_id = null;

            $this->dispatch('notify', message: 'Thanh toán thành công!', type: 'success');
            
            // Removed redirect to stay on POS page
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.pos.pos-terminal', [
            'products' => $this->getProducts(),
            'total' => $this->total,
            'finalAmount' => $this->finalAmount,
            'changeAmount' => $this->changeAmount,
            'customers' => $this->customers,
            'selectedCustomer' => $this->selectedCustomer,
            'categories_list' => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
            'brands_list' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'box_codes_list' => Product::whereNotNull('location')->distinct()->pluck('location'),
        ])->layout('layouts.app');
    }
}
