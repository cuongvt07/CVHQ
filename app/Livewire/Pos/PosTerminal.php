<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use Livewire\Component;

class PosTerminal extends Component
{
    public $cart = [];
    public $search = '';
    public $category = 'All';

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
            }
        }
    }

    public function removeFromCart($productId)
    {
        $this->cart = collect($this->cart)
            ->filter(fn($item) => $item['id'] !== $productId)
            ->values()
            ->toArray();
            
        $this->dispatch('notify', message: 'Đã xóa khỏi giỏ hàng', type: 'warning');
    }

    public function getProducts()
    {
        return Product::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->category !== 'All', fn($q) => $q->where('category_path', $this->category))
            ->where('is_active', true)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category_path' => $product->category_path,
                    'sale_price' => (int) $product->sale_price,
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
            'new_customer.phone' => 'required|digits_between:10,11',
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

        if ($this->paid_amount < $this->finalAmount) {
            $this->dispatch('notify', message: 'Số tiền khách trả chưa đủ!', type: 'error');
            return;
        }

        \DB::beginTransaction();
        try {
            $invoice = \App\Models\Invoice::create([
                'invoice_code' => 'HD' . time(),
                'branch' => 'Antigravity HQ',
                'customer_id' => $this->customer_id,
                'seller_name' => 'Admin POS',
                'sales_channel' => 'POS',
                'total_amount' => $this->total,
                'discount_amount' => $this->discount,
                'extra_fee' => $this->extra_fee,
                'final_amount' => $this->finalAmount,
                'paid_amount' => $this->paid_amount,
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
                    'final_price' => $item['sale_price'] * $item['quantity']
                ]);
            }

            \DB::commit();

            // Clear cart and reset
            $this->cart = [];
            $this->discount = 0;
            $this->extra_fee = 0;
            $this->paid_amount = 0;
            $this->customer_id = null;

            $this->dispatch('notify', message: 'Thanh toán thành công!', type: 'success');
            
            return redirect()->route('invoices.detail', $invoice->id);
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
            'selectedCustomer' => $this->selectedCustomer
        ])->layout('layouts.app');
    }
}
