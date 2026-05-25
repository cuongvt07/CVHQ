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

    // ── Shared product filters (across all tabs) ────────────────────────────
    public $search = '';
    public $category = 'All';
    public $selectedCategories = [];
    public $boxCode = '';
    public $brandFilter = '';
    public $stockStatus = 'all';

    // ── Multi-tab state ─────────────────────────────────────────────────────
    public array $tabs = [];
    public int $activeTab = 0;
    protected $listeners = [
        'restoreTabs',
        'renameTab',
        'addTab',
        'closeActiveTab',
        'duplicateTab',
        'moveTabLeft',
        'moveTabRight',
        'nextTab',
        'prevTab',
        'switchTab',
        'reorderTabs'
    ];

    // ── Customer search UI state (not per-tab) ──────────────────────────────
    public $customer_search = '';
    public $show_customer_search = false;
    public $is_creating_customer = false;
    public $new_customer = ['full_name' => '', 'phone' => ''];

    // ═══════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ═══════════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $this->tabs = [$this->makeNewTab()];
        $this->activeTab = 0;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TAB MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    protected function makeNewTab(?string $label = null): array
    {
        $tabNumber = count($this->tabs) + 1;
        return [
            'label'                => $label ?? ('Đơn ' . $tabNumber),
            'cart'                 => [],
            'customer_id'          => null,
            'discount'             => 0,
            'global_discount_type' => 'vnd',
            'global_discount_value'=> 0,
            'extra_fees'           => [],   // [{name:'', amount:0}, ...]
            'paid_amount'          => 0,
        ];
    }

    public function addTab(): void
    {
        if (count($this->tabs) >= 8) {
            $this->dispatch('notify', message: 'Tối đa 8 đơn cùng lúc!', type: 'warning');
            return;
        }
        $this->tabs[] = $this->makeNewTab();
        $this->activeTab = count($this->tabs) - 1;
        $this->resetCustomerSearch();
    }

    public function switchTab(int $index): void
    {
        if (isset($this->tabs[$index])) {
            $this->activeTab = $index;
            $this->resetCustomerSearch();
        }
    }

    public function closeTab(int $index): void
    {
        if (count($this->tabs) <= 1) {
            // Reset the only tab instead of closing
            $this->tabs[0] = $this->makeNewTab('Đơn 1');
            $this->activeTab = 0;
            $this->resetCustomerSearch();
            return;
        }

        array_splice($this->tabs, $index, 1);
        $this->tabs = array_values($this->tabs);

        if ($this->activeTab >= count($this->tabs)) {
            $this->activeTab = count($this->tabs) - 1;
        } elseif ($this->activeTab > $index) {
            $this->activeTab--;
        }

        $this->resetCustomerSearch();
    }

    protected function resetCustomerSearch(): void
    {
        $this->customer_search     = '';
        $this->show_customer_search = false;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ACTIVE TAB HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    protected function getTab(): array
    {
        return $this->tabs[$this->activeTab] ?? $this->makeNewTab();
    }

    protected function setTab(array $tab): void
    {
        $this->tabs[$this->activeTab] = $tab;
        $this->persistTabs();
    }

    protected function persistTabs(): void
    {
        $this->dispatchBrowserEvent('posTabsUpdate', ['tabs' => $this->tabs, 'active' => $this->activeTab]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // FILTER METHODS
    // ═══════════════════════════════════════════════════════════════════════

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatedBoxCode(): void      { $this->resetPage(); }
    public function updatedSelectedCategories() { $this->resetPage(); }

    public function toggleCategory($cat): void
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

    // PRODUCTS QUERY
    // ═══════════════════════════════════════════════════════════════════════

    public function getProducts()
    {
        return Product::query()
            ->when($this->search, function ($query) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('base_name', 'like', "%{$keyword}%")
                          ->orWhere('sku', 'like', "%{$keyword}%")
                          ->orWhere('brand', 'like', "%{$keyword}%")
                          ->orWhere('location', 'like', "%{$keyword}%");
                    });
                }
                $query->orderByRaw('CASE
                    WHEN sku = ? THEN 1
                    WHEN sku LIKE ? THEN 2
                    WHEN name LIKE ? THEN 3
                    ELSE 4
                END', [$this->search, $this->search . '%', $this->search . '%']);
            })
            ->when($this->selectedCategories, fn($q) => $q->whereIn('category_path', $this->selectedCategories))
            ->when($this->boxCode,            fn($q) => $q->where('location', 'like', "%{$this->boxCode}%"))
            ->when($this->brandFilter,        fn($q) => $q->where('brand', $this->brandFilter))
            ->when($this->stockStatus !== 'all', function ($query) {
                match ($this->stockStatus) {
                    'in_stock'     => $query->where('stock_quantity', '>', 0),
                    'low_stock'    => $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10),
                    'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                    default        => null,
                };
            })
            ->where('is_active', true)
            ->orderBy('sku', 'asc')
            ->paginate(24)
            ->onEachSide(1)
            ->through(fn($product) => [
                'id'            => $product->id,
                'sku'           => $product->sku,
                'name'          => $product->name,
                'base_name'     => $product->base_name,
                'category_path' => $product->category_path,
                'sale_price'    => (int) $product->sale_price,
                'location'      => $product->location,
                'stock_quantity'=> $product->stock_quantity,
                'image'         => !empty($product->images) ? $product->images[0] : null,
            ]);
    }

    // Close currently active tab (listener helper)
    public function closeActiveTab(): void
    {
        $this->closeTab($this->activeTab);
    }

    // Rename a tab
    public function renameTab(int $index, string $label): void
    {
        if (!isset($this->tabs[$index])) return;
        $this->tabs[$index]['label'] = trim($label) ?: $this->tabs[$index]['label'];
        $this->persistTabs();
        $this->dispatch('notify', message: 'Đã đổi tên tab', type: 'success');
    }

    // Duplicate a tab
    public function duplicateTab(int $index): void
    {
        if (!isset($this->tabs[$index])) return;
        if (count($this->tabs) >= 8) {
            $this->dispatch('notify', message: 'Tối đa 8 đơn cùng lúc!', type: 'warning');
            return;
        }
        $copy = $this->tabs[$index];
        $copy['label'] = $copy['label'] . ' (copy)';
        $this->tabs[] = $copy;
        $this->activeTab = count($this->tabs) - 1;
        $this->persistTabs();
    }

    // Move tab left / right
    public function moveTabLeft(int $index): void
    {
        if ($index <= 0 || !isset($this->tabs[$index])) return;
        $tabs = $this->tabs;
        $temp = $tabs[$index - 1];
        $tabs[$index - 1] = $tabs[$index];
        $tabs[$index] = $temp;
        $this->tabs = array_values($tabs);
        if ($this->activeTab === $index) $this->activeTab = $index - 1;
        elseif ($this->activeTab === $index - 1) $this->activeTab = $index;
        $this->persistTabs();
    }

    public function moveTabRight(int $index): void
    {
        if (!isset($this->tabs[$index]) || $index >= count($this->tabs) - 1) return;
        $tabs = $this->tabs;
        $temp = $tabs[$index + 1];
        $tabs[$index + 1] = $tabs[$index];
        $tabs[$index] = $temp;
        $this->tabs = array_values($tabs);
        if ($this->activeTab === $index) $this->activeTab = $index + 1;
        elseif ($this->activeTab === $index + 1) $this->activeTab = $index;
        $this->persistTabs();
    }

    // Cycle next/prev tab (wrap-around)
    public function nextTab(): void
    {
        $count = count($this->tabs);
        if ($count <= 1) return;
        $this->activeTab = ($this->activeTab + 1) % $count;
        $this->persistTabs();
    }

    public function prevTab(): void
    {
        $count = count($this->tabs);
        if ($count <= 1) return;
        $this->activeTab = ($this->activeTab - 1 + $count) % $count;
        $this->persistTabs();
    }

    // Reorder tabs via drag & drop (from index -> to index)
    public function reorderTabs(int $from, int $to): void
    {
        if (!isset($this->tabs[$from]) || $from === $to) return;

        $tabs = $this->tabs;
        $item = $tabs[$from];
        array_splice($tabs, $from, 1);
        if ($to > $from) $to = $to - 1; // adjust target after removal
        array_splice($tabs, $to, 0, [$item]);

        // adjust active index
        $active = $this->activeTab;
        if ($active === $from) {
            $newActive = $to;
        } elseif ($from < $active && $to >= $active) {
            $newActive = $active - 1;
        } elseif ($from > $active && $to <= $active) {
            $newActive = $active + 1;
        } else {
            $newActive = $active;
        }

        $this->tabs = array_values($tabs);
        $this->activeTab = max(0, min($newActive, count($this->tabs) - 1));
        $this->persistTabs();
    }

    // Hook: fires when wire:model updates nested tabs data
    public function updated($name): void
    {
        if (
            str_starts_with($name, 'tabs') &&
            str_contains($name, 'global_discount_value')
        ) {
            $this->recalculateTotalDiscount();
        }

        // Persist tabs to browser localStorage via a browser event
        if (str_starts_with($name, 'tabs')) {
            $this->dispatchBrowserEvent('posTabsUpdate', ['tabs' => $this->tabs, 'active' => $this->activeTab]);
        }
    }

    // Restore tabs state from frontend (localStorage)
    public function restoreTabs($payload): void
    {
        if (!is_array($payload)) return;
        $tabs = $payload['tabs'] ?? null;
        if (!is_array($tabs) || count($tabs) === 0) return;

        $sanitized = [];
        foreach ($tabs as $t) {
            $sanitized[] = [
                'label' => $t['label'] ?? 'Đơn',
                'cart' => $t['cart'] ?? [],
                'customer_id' => $t['customer_id'] ?? null,
                'discount' => (int) ($t['discount'] ?? 0),
                'global_discount_type' => $t['global_discount_type'] ?? 'vnd',
                'global_discount_value' => (float) ($t['global_discount_value'] ?? 0),
                'extra_fees' => $t['extra_fees'] ?? [],
                'paid_amount' => (int) ($t['paid_amount'] ?? 0),
            ];
        }

        $this->tabs = array_values($sanitized);
        $active = (int) ($payload['active'] ?? 0);
        $this->activeTab = min(max(0, $active), count($this->tabs) - 1);
        $this->recalculateTotalDiscount();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // EXTRA FEES
    // ═══════════════════════════════════════════════════════════════════════

    public function addExtraFee(): void
    {
        $tab = $this->getTab();
        $tab['extra_fees'][] = ['name' => '', 'amount' => 0];
        $this->setTab($tab);
    }

    public function removeExtraFee(int $feeIndex): void
    {
        $tab = $this->getTab();
        array_splice($tab['extra_fees'], $feeIndex, 1);
        $tab['extra_fees'] = array_values($tab['extra_fees']);
        $this->setTab($tab);
    }

    public function updateExtraFee(int $feeIndex, string $field, $value): void
    {
        $tab = $this->getTab();
        if (isset($tab['extra_fees'][$feeIndex])) {
            $tab['extra_fees'][$feeIndex][$field] = ($field === 'amount') ? (int)$value : $value;
        }
        $this->setTab($tab);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CUSTOMER MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    public function getCustomersProperty()
    {
        if (strlen($this->customer_search) < 2) return [];
        return \App\Models\Customer::query()
            ->where('full_name', 'like', "%{$this->customer_search}%")
            ->orWhere('phone', 'like', "%{$this->customer_search}%")
            ->orWhere('customer_code', 'like', "%{$this->customer_search}%")
            ->latest()->take(5)->get();
    }

    public function selectCustomer($id): void
    {
        $tab = $this->getTab();
        $tab['customer_id'] = $id;
        $this->setTab($tab);
        $this->customer_search      = '';
        $this->show_customer_search = false;
    }

    public function clearCustomer(): void
    {
        $tab = $this->getTab();
        $tab['customer_id'] = null;
        $this->setTab($tab);
    }

    public function createCustomer(): void
    {
        $this->validate([
            'new_customer.full_name' => 'required|min:2',
            'new_customer.phone'     => 'nullable|digits_between:10,11',
        ]);

        $customer = \App\Models\Customer::create([
            'full_name'     => $this->new_customer['full_name'],
            'phone'         => $this->new_customer['phone'],
            'customer_code' => 'KH' . time(),
            'status'        => 'Active',
        ]);
            $this->persistTabs();

        $this->selectCustomer($customer->id);
        $this->is_creating_customer = false;
        $this->new_customer         = ['full_name' => '', 'phone' => ''];
        $this->dispatch('notify', message: 'Tạo khách hàng mới thành công!', type: 'success');
    }

    public function getSelectedCustomerProperty()
    {
        $id = $this->getTab()['customer_id'] ?? null;
        return $id ? \App\Models\Customer::find($id) : null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // COMPUTED FINANCIALS
    // ═══════════════════════════════════════════════════════════════════════

    public function getTotalProperty(): int
    {
        return (int) collect($this->getTab()['cart'])
            ->sum(fn($item) => $item['sale_price'] * $item['quantity']);
    }

    public function getExtraFeeTotalProperty(): int
    {
        return (int) collect($this->getTab()['extra_fees'] ?? [])
            ->sum(fn($f) => (int)($f['amount'] ?? 0));
    }

    public function getFinalAmountProperty(): int
    {
        $tab = $this->getTab();
        return max(0, $this->total - (int)($tab['discount'] ?? 0) + $this->extraFeeTotal);
    }

    public function getChangeAmountProperty(): int
    {
        $paid = (int)($this->getTab()['paid_amount'] ?? 0);
        if (!$paid) return 0;
        return max(0, $paid - $this->finalAmount);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CHECKOUT
    // ═══════════════════════════════════════════════════════════════════════

    public function checkout(): void
    {
        $tab = $this->getTab();

        if (empty($tab['cart'])) {
            $this->dispatch('notify', message: 'Giỏ hàng đang trống!', type: 'error');
            return;
        }

        \DB::beginTransaction();
        try {
            $canReceiveCommission = auth()->user()->can_receive_commission;
            $totalCommission = $canReceiveCommission
                ? collect($tab['cart'])->sum(fn($item) => $item['commission_amount'] * $item['quantity'])
                : 0;

            $extraFeeTotal = $this->extraFeeTotal;
            $extraFeeName  = collect($tab['extra_fees'] ?? [])
                ->filter(fn($f) => !empty($f['name']) && (int)($f['amount'] ?? 0) > 0)
                ->map(fn($f) => $f['name'] . ' (' . number_format((int)$f['amount'], 0, ',', '.') . 'đ)')
                ->join('; ');

            $invoice = \App\Models\Invoice::create([
                'invoice_code'   => 'HD' . time(),
                'branch'         => 'Antigravity HQ',
                'customer_id'    => $tab['customer_id'],
                'user_id'        => auth()->id(),
                'seller_name'    => auth()->user()?->name ?? 'Admin POS',
                'sales_channel'  => 'POS',
                'total_amount'   => $this->total,
                'discount_amount'=> $tab['discount'] ?? 0,
                'extra_fee'      => $extraFeeTotal,
                'extra_fee_name' => $extraFeeName ?: null,
                'final_amount'   => $this->finalAmount,
                'total_commission'=> $totalCommission,
                'paid_amount'    => $this->finalAmount,
                'status'         => 'Completed',
                'delivery_status'=> 'Delivered',
            ]);

            foreach ($tab['cart'] as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $item['id'],
                    'sku'              => $item['sku'],
                    'product_name'     => $item['name'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['sale_price'],
                    'commission_amount'=> $canReceiveCommission ? $item['commission_amount'] : 0,
                    'final_price'      => $item['sale_price'] * $item['quantity'],
                ]);

                $product = \App\Models\Product::find($item['id']);
                if ($product) {
                    $product->recordStockHistory(
                        'Sale', -$item['quantity'],
                        $invoice->id, $invoice->invoice_code, 'Bán hàng'
                    );
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }
                            $this->persistTabs();

            \DB::commit();

            // Reset the current tab (keep label)
            $label = $tab['label'];
            $this->tabs[$this->activeTab] = $this->makeNewTab($label);

            $this->dispatch('notify', message: 'Thanh toán thành công! 🎉', type: 'success');

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }
    // ═══════════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════════

    public function render()
    {
        $tab = $this->getTab();
        return view('livewire.pos.pos-terminal', [
            'products'           => $this->getProducts(),
            'cart'               => $tab['cart'] ?? [],
            'total'              => $this->total,
            'finalAmount'        => $this->finalAmount,
            'changeAmount'       => $this->changeAmount,
            'extraFeeTotal'      => $this->extraFeeTotal,
            'customers'          => $this->customers,
            'selectedCustomer'   => $this->selectedCustomer,
            'currentTab'         => $tab,
            'global_discount_type' => $tab['global_discount_type'] ?? 'vnd',
            'extra_fees'         => $tab['extra_fees'] ?? [],
            'categories_list'    => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
            'brands_list'        => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'box_codes_list'     => Product::whereNotNull('location')->distinct()->pluck('location'),
        ])->layout('layouts.app');
    }
}
