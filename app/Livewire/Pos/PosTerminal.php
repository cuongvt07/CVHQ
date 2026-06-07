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
    public $branch = 'all'; // 'all' | 'sg' | 'hn' — same convention as ProductIndex
    public $brandFilter = '';
    public $stockStatus = 'all';

    // ── Multi-tab state ─────────────────────────────────────────────────────
    public array $tabs = [];
    public int $activeTab = 0;
    protected $listeners = [
        'restoreTabs',
        'restoreBranch',
        'addTab',
        'closeActiveTab',
        'nextTab',
        'prevTab',
        'switchTab',
    ];

    // ── Customer search UI state (not per-tab) ──────────────────────────────
    public $customer_search = '';
    public $show_customer_search = false;
    public $is_creating_customer = false;
    public $new_customer = ['full_name' => '', 'phone' => ''];

    // ── Commission sharing (per-checkout, reset after checkout) ─────────────
    public $sharedToUserId = null;
    public $sharedCommissionAmount = '';

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

    // Hardcoded sales channels — edit here to add/remove
    public const SALES_CHANNELS = [
        ['name' => 'Trực tiếp', 'color' => '#0088CC'],
        ['name' => 'Shopee',    'color' => '#EE4D2D'],
        ['name' => 'TikTok',    'color' => '#000000'],
        ['name' => 'Facebook',  'color' => '#1877F2'],
        ['name' => 'Zalo',      'color' => '#0068FF'],
        ['name' => 'Email',     'color' => '#94A3B8'],
    ];

    // Hardcoded payment methods — keys map to invoices.{cash,transfer}_amount columns
    public const PAYMENT_METHODS = [
        ['key' => 'cash',     'name' => 'Tiền mặt',     'icon' => 'wallet'],
        ['key' => 'transfer', 'name' => 'Chuyển khoản', 'icon' => 'send'],
    ];

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
            'extra_fees'           => [],
            'paid_amount'          => 0,
            'sales_channel'        => self::SALES_CHANNELS[0]['name'],
            'payment_method'       => self::PAYMENT_METHODS[0]['key'],
        ];
    }

    public function setSalesChannel($name = null): void
    {
        $tab = $this->getTab();
        $valid = array_column(self::SALES_CHANNELS, 'name');
        $tab['sales_channel'] = (is_string($name) && in_array($name, $valid, true)) ? $name : null;
        $this->setTab($tab);
    }

    public function setPaymentMethod($key = null): void
    {
        $tab = $this->getTab();
        $valid = array_column(self::PAYMENT_METHODS, 'key');
        $tab['payment_method'] = (is_string($key) && in_array($key, $valid, true)) ? $key : 'cash';
        $this->setTab($tab);
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
        // Livewire v4: $this->dispatch reaches browser via Livewire.on('posTabsUpdate', ...)
        $this->dispatch('posTabsUpdate', tabs: $this->tabs, active: $this->activeTab);
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
            ->when($this->branch !== 'all', function ($query) {
                if ($this->branch === 'sg') {
                    $query->where('sku', 'LIKE', 'Z%');
                } else {
                    $query->where('sku', 'NOT LIKE', 'Z%');
                }
            })
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

    // Hook: fires when wire:model updates nested tabs data
    public function updated($name, $value = null): void
    {
        if (str_starts_with($name, 'tabs')) {
            $this->recalculateTotalDiscount();
            $this->dispatch('posTabsUpdate', tabs: $this->tabs, active: $this->activeTab);
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
                'sales_channel' => isset($t['sales_channel']) && in_array($t['sales_channel'], array_column(self::SALES_CHANNELS, 'name'), true)
                                    ? $t['sales_channel']
                                    : self::SALES_CHANNELS[0]['name'],
                'payment_method' => isset($t['payment_method']) && in_array($t['payment_method'], array_column(self::PAYMENT_METHODS, 'key'), true)
                                    ? $t['payment_method']
                                    : self::PAYMENT_METHODS[0]['key'],
            ];
        }

        $this->tabs = array_values($sanitized);
        $active = (int) ($payload['active'] ?? 0);
        $this->activeTab = min(max(0, $active), count($this->tabs) - 1);
        $this->recalculateTotalDiscount();
    }

    public function restoreBranch(string $branch): void
    {
        if (in_array($branch, ['all', 'sg', 'hn'], true)) {
            $this->branch = $branch;
        }
    }

    public function setBranch(string $value): void
    {
        if (!in_array($value, ['all', 'sg', 'hn'], true)) {
            $value = 'all';
        }
        $this->branch = $value;
        $this->dispatch('posBranchUpdate', branch: $this->branch);
        $this->resetPage();
    }

    public function updatedBranch(): void
    {
        $this->dispatch('posBranchUpdate', branch: $this->branch);
        $this->resetPage();
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
    // CART OPERATIONS (all per-tab via getTab/setTab)
    // ═══════════════════════════════════════════════════════════════════════

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            $this->dispatch('notify', message: 'Sản phẩm không tồn tại!', type: 'error');
            return;
        }

        $stock = (int) ($product->stock_quantity ?? 0);

        // Hết hàng: không cho thêm
        if ($stock <= 0) {
            $this->dispatch('notify', message: 'Sản phẩm "' . $product->name . '" đã hết hàng!', type: 'warning');
            return;
        }

        $tab = $this->getTab();

        $found = false;
        foreach ($tab['cart'] as &$item) {
            if ((int)$item['id'] === (int)$productId) {
                // Kiểm tra vượt tồn kho trước khi tăng
                if ($item['quantity'] + 1 > $stock) {
                    $this->dispatch('notify', message: 'Vượt tồn kho! Còn lại ' . $stock . ' "' . $product->name . '"', type: 'warning');
                    return;
                }
                $item['quantity']++;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $tab['cart'][] = [
                'id'                  => $product->id,
                'sku'                 => $product->sku,
                'name'                => $product->name,
                'sale_price'          => (int) $product->sale_price,
                'original_price'      => (int) $product->sale_price,
                'quantity'            => 1,
                'commission_amount'   => (int) ($product->commission_amount ?? 0),
                'image'               => !empty($product->images) ? $product->images[0] : null,
                'discount'            => 0,
                'calculated_discount' => 0,
            ];
        }

        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function updateQuantity(int $productId, int $delta): void
    {
        $tab = $this->getTab();
        foreach ($tab['cart'] as $i => &$item) {
            if ((int)$item['id'] === (int)$productId) {
                $newQty = max(0, (int)$item['quantity'] + $delta);

                // Khi tăng số lượng, kiểm tra vượt tồn kho
                if ($delta > 0) {
                    $stock = (int) (Product::where('id', $productId)->value('stock_quantity') ?? 0);
                    if ($newQty > $stock) {
                        $this->dispatch('notify', message: 'Vượt tồn kho! Còn lại ' . $stock . ' "' . $item['name'] . '"', type: 'warning');
                        return;
                    }
                }

                $item['quantity'] = $newQty;
                if ($item['quantity'] === 0) {
                    array_splice($tab['cart'], $i, 1);
                    $tab['cart'] = array_values($tab['cart']);
                }
                break;
            }
        }
        unset($item);
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function setQuantity(int $productId, $value): void
    {
        $qty = max(0, (int) $value);
        $tab = $this->getTab();
        foreach ($tab['cart'] as $i => &$item) {
            if ((int)$item['id'] === (int)$productId) {
                // Cap qty tại tồn kho khi gõ trực tiếp
                if ($qty > 0) {
                    $stock = (int) (Product::where('id', $productId)->value('stock_quantity') ?? 0);
                    if ($qty > $stock) {
                        $this->dispatch('notify', message: 'Số lượng vượt tồn kho. Đã giới hạn ở ' . $stock, type: 'warning');
                        $qty = $stock;
                    }
                }

                if ($qty === 0) {
                    array_splice($tab['cart'], $i, 1);
                    $tab['cart'] = array_values($tab['cart']);
                } else {
                    $item['quantity'] = $qty;
                }
                break;
            }
        }
        unset($item);
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function removeFromCart(int $productId): void
    {
        $tab = $this->getTab();
        $tab['cart'] = array_values(array_filter(
            $tab['cart'],
            fn($item) => (int)$item['id'] !== (int)$productId
        ));
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function applyItemDiscount(int $productId, $value): void
    {
        $discount = max(0, (int) $value);
        $tab = $this->getTab();
        foreach ($tab['cart'] as &$item) {
            if ((int)$item['id'] === (int)$productId) {
                $item['discount'] = $discount;
                break;
            }
        }
        unset($item);
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function updateUnitPrice(int $productId, $value): void
    {
        $price = max(0, (int) $value);
        $tab = $this->getTab();
        foreach ($tab['cart'] as &$item) {
            if ((int)$item['id'] === (int)$productId) {
                // Lưu original_price nếu chưa có (lần đầu sửa giá cho item cũ từ localStorage)
                if (!isset($item['original_price'])) {
                    $item['original_price'] = (int) $item['sale_price'];
                }
                $item['sale_price'] = $price;
                break;
            }
        }
        unset($item);
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function resetUnitPrice(int $productId): void
    {
        $tab = $this->getTab();
        foreach ($tab['cart'] as &$item) {
            if ((int)$item['id'] === (int)$productId && isset($item['original_price'])) {
                $item['sale_price'] = (int) $item['original_price'];
                break;
            }
        }
        unset($item);
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function setGlobalDiscountType(string $type): void
    {
        if (!in_array($type, ['vnd', '%'], true)) return;
        $tab = $this->getTab();
        $tab['global_discount_type'] = $type;
        $this->setTab($tab);
        $this->recalculateTotalDiscount();
    }

    public function recalculateTotalDiscount(): void
    {
        $tab  = $this->getTab();
        $cart = $tab['cart'] ?? [];

        $lineSubtotal = fn($item) => (int)$item['sale_price'] * (int)$item['quantity'];
        $total        = array_sum(array_map($lineSubtotal, $cart));
        $type         = $tab['global_discount_type'] ?? 'vnd';
        $rawValue     = (float)($tab['global_discount_value'] ?? 0);

        if ($type === '%') {
            $pct = max(0.0, min(100.0, $rawValue));
            foreach ($cart as $i => $item) {
                $cart[$i]['calculated_discount'] = (int) round($lineSubtotal($item) * $pct / 100);
            }
            $globalDiscount = (int) array_sum(array_column($cart, 'calculated_discount'));
        } else {
            foreach ($cart as $i => $item) {
                $cart[$i]['calculated_discount'] = 0;
            }
            $globalDiscount = (int) max(0, min($rawValue, $total));
        }

        $itemDiscountTotal = (int) array_sum(array_map(fn($it) => (int)($it['discount'] ?? 0), $cart));

        $tab['cart']     = $cart;
        $tab['discount'] = $globalDiscount + $itemDiscountTotal;
        $this->setTab($tab);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // FILTER CHIP CLEAR (gallery filters are shared across tabs)
    // ═══════════════════════════════════════════════════════════════════════

    public function clearFilter(string $filter, ?string $value = null): void
    {
        if ($filter === 'all') {
            $this->search = '';
            $this->selectedCategories = [];
            $this->category = 'All';
            $this->boxCode = '';
            $this->branch = 'all';
            $this->dispatch('posBranchUpdate', branch: 'all');
            $this->resetPage();
            return;
        }
        if ($filter === 'search') {
            $this->search = '';
        } elseif ($filter === 'boxCode') {
            $this->boxCode = '';
        } elseif ($filter === 'selectedCategories' && $value !== null) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$value]));
            if (empty($this->selectedCategories)) {
                $this->category = 'All';
            }
        }
        $this->resetPage();
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

            $channelName = $tab['sales_channel'] ?? null;
            if (!$channelName || !in_array($channelName, array_column(self::SALES_CHANNELS, 'name'), true)) {
                $channelName = 'POS';
            }
            $paymentKey = $tab['payment_method'] ?? 'cash';
            $validPaymentKeys = array_column(self::PAYMENT_METHODS, 'key');
            if (!in_array($paymentKey, $validPaymentKeys, true)) $paymentKey = 'cash';

            $invoiceBranch = auth()->user()?->work_branch ?: $this->branch;
            if ($invoiceBranch === 'all') $invoiceBranch = 'hn';
            if (!in_array($invoiceBranch, ['sg', 'hn'], true)) {
                $invoiceBranch = 'hn';
            }

            // Allocate final amount into the matching payment column
            $paymentColumns = [
                'cash_amount'     => 0,
                'transfer_amount' => 0,
                'card_amount'     => 0,
                'wallet_amount'   => 0,
            ];
            $paymentColumns[$paymentKey . '_amount'] = $this->finalAmount;

            $invoice = \App\Models\Invoice::create(array_merge([
                'invoice_code'   => 'HD' . time(),
                'branch'         => $invoiceBranch,
                'customer_id'    => $tab['customer_id'],
                'user_id'        => auth()->id(),
                'seller_name'    => auth()->user()?->name ?? 'Admin POS',
                'sales_channel'  => $channelName,
                'created_at'     => now(),
                'total_amount'   => $this->total,
                'discount_amount'=> $tab['discount'] ?? 0,
                'extra_fee'      => $extraFeeTotal,
                'extra_fee_name' => $extraFeeName ?: null,
                'final_amount'   => $this->finalAmount,
                'total_commission'=> $totalCommission,
                'shared_commission_amount' => ($this->sharedToUserId && $this->sharedCommissionAmount !== '')
                    ? max(0, min((int) $this->sharedCommissionAmount, $totalCommission))
                    : null,
                'shared_to_user_id' => ($this->sharedToUserId && $this->sharedCommissionAmount !== '')
                    ? (int) $this->sharedToUserId
                    : null,
                'paid_amount'    => $this->finalAmount,
                'status'         => 'Completed',
                'delivery_status'=> 'Delivered',
            ], $paymentColumns));

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
            $this->sharedToUserId = null;
            $this->sharedCommissionAmount = '';

            $this->dispatch('notify', message: 'Thanh toán thành công! 🎉', type: 'success');

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
        }
    }
    // ═══════════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════════

    public function getStaffList(): \Illuminate\Support\Collection
    {
        return \App\Models\User::where('id', '!=', auth()->id())
            ->where('can_receive_commission', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        $tab = $this->getTab();
        $cart = $tab['cart'] ?? [];
        $canReceiveCommission = auth()->user()->can_receive_commission ?? false;
        $totalCommission = $canReceiveCommission
            ? (int) collect($cart)->sum(fn($item) => $item['commission_amount'] * $item['quantity'])
            : 0;

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
            'sales_channels'        => self::SALES_CHANNELS,
            'payment_methods'       => self::PAYMENT_METHODS,
            'totalCommission'       => $totalCommission,
            'canReceiveCommission'  => $canReceiveCommission,
            'staffList'             => $canReceiveCommission ? $this->getStaffList() : collect(),
        ])->layout('layouts.app');
    }
}
