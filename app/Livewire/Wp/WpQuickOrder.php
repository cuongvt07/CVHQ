<?php

namespace App\Livewire\Wp;

use App\Models\WpOrder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Livewire\Pos\PosTerminal;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

/**
 * Slide-over tạo đơn NHANH từ 1 đơn WooCommerce.
 * Panel WP chỉ để tham chiếu (biết khách + SP gì) — đơn nội bộ tạo THỦ CÔNG
 * (nhân viên tự tìm & chọn sản phẩm).
 */
class WpQuickOrder extends Component
{
    public bool $open = false;
    public ?int $wpOrderId = null;
    public array $wpRef = [];   // dữ liệu WP để hiển thị tham chiếu

    // Đơn tạo tay
    public array $cart = [];    // [{id, sku, name, price, qty, commission, stock}]
    public string $productSearch = '';
    public string $custName = '';
    public string $custPhone = '';
    public string $custAddress = '';
    public string $channel = 'Website';
    public string $paymentMethod = 'cash';
    public int $shippingFee = 0;
    public $discount = 0;

    #[On('open-wp-quick')]
    public function openFor($id): void
    {
        $o = WpOrder::find($id);
        if (!$o) {
            return;
        }
        $this->wpOrderId = $o->id;
        $this->wpRef = [
            'number' => $o->number,
            'status' => $o->status,
            'total' => $o->total,
            'shipping' => $o->shipping_total,
            'payment' => $o->payment_title,
            'note' => $o->customer_note,
            'items' => $o->items ?? [],
            'handled' => $o->local_invoice_id ? true : false,
        ];
        // Prefill thông tin khách (tiện, vẫn sửa được) — nhưng GIỎ HÀNG để trống (tạo tay).
        $this->custName = (string) $o->customer_name;
        $this->custPhone = (string) $o->customer_phone;
        $this->custAddress = (string) $o->address;
        $this->shippingFee = (int) $o->shipping_total;
        $this->cart = [];
        $this->productSearch = '';
        $this->discount = 0;
        $this->paymentMethod = 'cash';
        $this->channel = 'Website';
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function getSearchResultsProperty()
    {
        $s = trim($this->productSearch);
        if ($s === '') {
            return collect();
        }
        return Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($s) {
                $q->where('sku', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%");
            })
            ->orderBy('sku')
            ->limit(20)
            ->get(['id', 'sku', 'name', 'sale_price', 'commission_amount', 'stock_quantity']);
    }

    public function channelOptions(): array
    {
        return array_merge(['Website'], array_column(PosTerminal::SALES_CHANNELS, 'name'));
    }

    public function addProduct($id): void
    {
        foreach ($this->cart as &$row) {
            if ($row['id'] === $id) {
                $row['qty']++;
                $this->productSearch = '';
                return;
            }
        }
        unset($row);

        $p = Product::find($id);
        if (!$p) {
            return;
        }
        $this->cart[] = [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'price' => (int) $p->sale_price,
            'qty' => 1,
            'commission' => (int) $p->commission_value,
            'stock' => (int) $p->stock_quantity,
        ];
        $this->productSearch = '';
    }

    public function setQty($i, $val): void
    {
        if (isset($this->cart[$i])) {
            $this->cart[$i]['qty'] = max(1, (int) $val);
        }
    }

    public function setPrice($i, $val): void
    {
        if (isset($this->cart[$i])) {
            $this->cart[$i]['price'] = max(0, (int) $val);
        }
    }

    public function removeItem($i): void
    {
        unset($this->cart[$i]);
        $this->cart = array_values($this->cart);
    }

    public function getSubtotalProperty(): int
    {
        return (int) collect($this->cart)->sum(fn ($r) => (int) $r['price'] * (int) $r['qty']);
    }

    public function getFinalProperty(): int
    {
        return max(0, $this->subtotal - (int) $this->discount + (int) $this->shippingFee);
    }

    public function createInvoice()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Giỏ hàng trống — hãy chọn sản phẩm.', type: 'warning');
            return;
        }

        $wp = WpOrder::find($this->wpOrderId);
        if ($wp && $wp->local_invoice_id) {
            $this->dispatch('notify', message: 'Đơn WP này đã được tạo hóa đơn rồi.', type: 'warning');
            $this->open = false;
            return;
        }

        try {
            DB::beginTransaction();

            // Khách hàng: tìm theo SĐT, chưa có thì tạo mới.
            $customerId = null;
            $phone = trim($this->custPhone);
            $customer = $phone !== '' ? Customer::where('phone', $phone)->first() : null;
            if (!$customer) {
                $customer = Customer::create([
                    'customer_code' => 'KH' . now()->format('ymdHis') . rand(10, 99),
                    'full_name' => $this->custName ?: 'Khách WP',
                    'phone' => $phone ?: null,
                    'address' => $this->custAddress ?: null,
                ]);
            }
            $customerId = $customer->id;

            $branch = auth()->user()?->work_branch;
            if (!in_array($branch, ['hn', 'sg'], true)) {
                $branch = 'hn';
            }

            $subtotal = $this->subtotal;
            $totalCommission = (int) collect($this->cart)->sum(fn ($r) => (int) $r['commission'] * (int) $r['qty']);
            $final = $this->final;

            $paymentKey = in_array($this->paymentMethod, ['cash', 'transfer'], true) ? $this->paymentMethod : 'cash';
            $paymentCols = ['cash_amount' => 0, 'transfer_amount' => 0, 'card_amount' => 0, 'wallet_amount' => 0];
            $paymentCols[$paymentKey . '_amount'] = $final;

            $invoice = Invoice::create(array_merge([
                'invoice_code' => 'WP' . time(),
                'branch' => $branch,
                'customer_id' => $customerId,
                'user_id' => auth()->id(),
                'seller_name' => auth()->user()?->name ?? 'Admin',
                'sales_channel' => $this->channel ?: 'Website',
                'created_at' => now(),
                'total_amount' => $subtotal,
                'discount_amount' => (int) $this->discount,
                'extra_fee' => (int) $this->shippingFee,
                'extra_fee_name' => $this->shippingFee > 0 ? 'Phí ship' : null,
                'final_amount' => $final,
                'total_commission' => $totalCommission,
                'paid_amount' => $final,
                'status' => 'Completed',
                'delivery_status' => 'Pending',
            ], $paymentCols));

            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['id'],
                    'sku' => $item['sku'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'commission_amount' => $item['commission'],
                    'final_price' => (int) $item['price'] * (int) $item['qty'],
                ]);

                $product = Product::find($item['id']);
                if ($product) {
                    $product->recordStockHistory('Sale', -(int) $item['qty'], $invoice->id, $invoice->invoice_code, 'Bán hàng (đơn WP #' . ($this->wpRef['number'] ?? '') . ')');
                    $product->decrement('stock_quantity', (int) $item['qty']);
                }
            }

            // Gắn ngược về đơn WP.
            if ($wp) {
                $wp->update([
                    'local_invoice_id' => $invoice->id,
                    'handled_at' => now(),
                    'handled_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $this->open = false;
            $this->dispatch('notify', message: 'Đã tạo hóa đơn ' . $invoice->invoice_code . ' từ đơn WP!', type: 'success');
            $this->dispatch('wp-order-created');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Lỗi tạo đơn: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.wp.wp-quick-order');
    }
}
