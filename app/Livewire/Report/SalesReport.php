<?php

namespace App\Livewire\Report;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Branch;
use App\Models\User;
use App\Livewire\Pos\PosTerminal;
use App\Exports\SalesReportExport;
use App\Traits\HasPermissions;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SalesReport extends Component
{
    use HasPermissions;

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public string $fromDate = '';
    public string $toDate = '';
    public string $branch = '';
    public string $sellerId = '';
    public string $channel = '';
    public string $groupBy = 'day'; // day | seller | channel | branch | product
    public array $visibleColumns = []; // cột được chọn để xuất (mặc định = tất cả cột hiển thị)

    protected $queryString = [
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'branch' => ['except' => ''],
        'sellerId' => ['except' => ''],
        'channel' => ['except' => ''],
        'groupBy' => ['except' => 'day'],
    ];

    public function mount(): void
    {
        if ($this->fromDate === '') {
            $this->fromDate = now()->startOfMonth()->toDateString();
        }
        if ($this->toDate === '') {
            $this->toDate = now()->toDateString();
        }
        if (empty($this->visibleColumns)) {
            $this->visibleColumns = array_keys($this->columnsFor());
        }
    }

    /** [key => tiêu đề] các cột của bảng breakdown theo chế độ nhóm. */
    public function columnsFor(?string $mode = null): array
    {
        $mode ??= $this->groupBy;
        if ($mode === 'product') {
            return ['label' => 'Sản phẩm', 'qty' => 'SL bán', 'revenue' => 'Doanh thu', 'cogs' => 'Giá vốn', 'profit' => 'Lợi nhuận tạm tính'];
        }
        $labelHead = match ($mode) {
            'seller' => 'Nhân viên',
            'channel' => 'Kênh bán',
            'branch' => 'Chi nhánh',
            default => 'Ngày',
        };
        return [
            'label' => $labelHead,
            'orders' => 'Số đơn',
            'qty' => 'SL bán',
            'revenue' => 'Doanh thu',
            'cogs' => 'Giá vốn',
            'commission' => 'Hoa hồng',
            'profit' => 'Lợi nhuận tạm tính',
        ];
    }

    /** Đổi chế độ nhóm -> reset chọn cột về tất cả cột hiển thị. */
    public function updatedGroupBy(): void
    {
        $this->visibleColumns = array_keys($this->columnsFor());
    }

    /** Bật/tắt cột hiển thị (cũng áp dụng cho file Excel xuất ra). */
    public function toggleColumn($col): void
    {
        if (in_array($col, $this->visibleColumns, true)) {
            $this->visibleColumns = array_values(array_diff($this->visibleColumns, [$col]));
        } elseif (array_key_exists($col, $this->columnsFor())) {
            $this->visibleColumns[] = $col;
        }
    }

    /** Cột đang hiển thị, giữ đúng thứ tự gốc. */
    public function shownColumns(): array
    {
        return array_filter($this->columnsFor(), fn ($k) => in_array($k, $this->visibleColumns, true), ARRAY_FILTER_USE_KEY);
    }

    /** Đặt nhanh khoảng thời gian. */
    public function setRange(string $preset): void
    {
        $today = now();
        match ($preset) {
            'today' => [$this->fromDate = $today->toDateString(), $this->toDate = $today->toDateString()],
            '7d' => [$this->fromDate = $today->copy()->subDays(6)->toDateString(), $this->toDate = $today->toDateString()],
            'month' => [$this->fromDate = $today->copy()->startOfMonth()->toDateString(), $this->toDate = $today->toDateString()],
            'lastmonth' => [
                $this->fromDate = $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $this->toDate = $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            default => null,
        };
    }

    /** Query hóa đơn hợp lệ theo bộ lọc. */
    protected function invoiceQuery()
    {
        $from = Carbon::parse($this->fromDate)->startOfDay();
        $to = Carbon::parse($this->toDate)->endOfDay();

        return Invoice::query()
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereBetween('created_at', [$from, $to])
            ->when($this->branch !== '', fn ($q) => $q->where('branch', $this->branch))
            ->when($this->sellerId !== '', fn ($q) => $q->where('user_id', $this->sellerId))
            ->when($this->channel !== '', fn ($q) => $q->where('sales_channel', $this->channel));
    }

    /** Query item (join hóa đơn + sản phẩm) cùng bộ lọc — dùng cho giá vốn & top SP. */
    protected function itemQuery()
    {
        $from = Carbon::parse($this->fromDate)->startOfDay();
        $to = Carbon::parse($this->toDate)->endOfDay();

        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereNull('invoices.deleted_at')
            ->whereNull('invoice_items.deleted_at')
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->whereBetween('invoices.created_at', [$from, $to])
            ->when($this->branch !== '', fn ($q) => $q->where('invoices.branch', $this->branch))
            ->when($this->sellerId !== '', fn ($q) => $q->where('invoices.user_id', $this->sellerId))
            ->when($this->channel !== '', fn ($q) => $q->where('invoices.sales_channel', $this->channel));
    }

    protected function summary(): array
    {
        $inv = $this->invoiceQuery()->selectRaw('
            COUNT(*) AS orders,
            COALESCE(SUM(total_amount),0) AS goods,
            COALESCE(SUM(discount_amount),0) AS discount,
            COALESCE(SUM(extra_fee),0) AS fee,
            COALESCE(SUM(final_amount),0) AS revenue,
            COALESCE(SUM(total_commission),0) AS commission
        ')->first();

        $items = $this->itemQuery()->selectRaw('
            COALESCE(SUM(invoice_items.quantity),0) AS qty,
            COALESCE(SUM(COALESCE(products.cost_price,0) * invoice_items.quantity),0) AS cogs
        ')->first();

        $goods = (int) $inv->goods;
        $discount = (int) $inv->discount;
        $cogs = (int) $items->cogs;
        $commission = (int) $inv->commission;

        return [
            'orders' => (int) $inv->orders,
            'goods' => $goods,
            'discount' => $discount,
            'fee' => (int) $inv->fee,
            'revenue' => (int) $inv->revenue,
            'commission' => $commission,
            'qty' => (int) $items->qty,
            'cogs' => $cogs,
            // Lợi nhuận tạm tính = (tiền hàng - giảm giá) - giá vốn - hoa hồng (không tính phụ phí vào lãi)
            'profit' => $goods - $discount - $cogs - $commission,
        ];
    }

    protected function breakdown(): array
    {
        if ($this->groupBy === 'product') {
            $rows = $this->itemQuery()
                ->selectRaw('
                    COALESCE(invoice_items.product_name, invoice_items.sku) AS label,
                    invoice_items.sku AS sku,
                    SUM(invoice_items.quantity) AS qty,
                    SUM(invoice_items.final_price) AS revenue,
                    SUM(COALESCE(products.cost_price,0) * invoice_items.quantity) AS cogs
                ')
                ->groupBy('label', 'invoice_items.sku')
                ->orderByDesc('revenue')
                ->limit(100)
                ->get();

            return [
                'mode' => 'product',
                'rows' => $rows->map(fn ($r) => [
                    'label' => $r->sku ? ($r->sku . ' — ' . $r->label) : $r->label,
                    'qty' => (int) $r->qty,
                    'revenue' => (int) $r->revenue,
                    'cogs' => (int) $r->cogs,
                    'profit' => (int) $r->revenue - (int) $r->cogs,
                ])->all(),
            ];
        }

        // Biểu thức nhóm: trên bảng invoices và trên bảng item (join) phải khớp nhau.
        [$invSelect, $itemSelect] = match ($this->groupBy) {
            'seller' => ["COALESCE(NULLIF(seller_name,''), 'Không rõ')", "COALESCE(NULLIF(invoices.seller_name,''), 'Không rõ')"],
            'channel' => ["COALESCE(NULLIF(sales_channel,''), 'Không rõ')", "COALESCE(NULLIF(invoices.sales_channel,''), 'Không rõ')"],
            'branch' => ["COALESCE(NULLIF(branch,''), 'Không rõ')", "COALESCE(NULLIF(invoices.branch,''), 'Không rõ')"],
            default => ["DATE(created_at)", "DATE(invoices.created_at)"],
        };

        $invAgg = $this->invoiceQuery()
            ->selectRaw("$invSelect AS label,
                COUNT(*) AS orders,
                COALESCE(SUM(total_amount),0) AS goods,
                COALESCE(SUM(discount_amount),0) AS discount,
                COALESCE(SUM(final_amount),0) AS revenue,
                COALESCE(SUM(total_commission),0) AS commission")
            ->groupBy('label')
            ->get()
            ->keyBy('label');

        // Số lượng + giá vốn theo cùng chiều nhóm.
        $itemAgg = $this->itemQuery()
            ->selectRaw("$itemSelect AS label,
                SUM(invoice_items.quantity) AS qty,
                SUM(COALESCE(products.cost_price,0) * invoice_items.quantity) AS cogs")
            ->groupBy('label')
            ->get()
            ->keyBy('label');

        $rows = $invAgg->map(function ($r, $key) use ($itemAgg) {
            $it = $itemAgg->get($key);
            $goods = (int) $r->goods;
            $discount = (int) $r->discount;
            $cogs = (int) ($it->cogs ?? 0);
            $commission = (int) $r->commission;
            return [
                'label' => $this->groupBy === 'branch' ? (Branch::nameOf((string) $key) ?: (string) $key) : (string) $key,
                'orders' => (int) $r->orders,
                'qty' => (int) ($it->qty ?? 0),
                'revenue' => (int) $r->revenue,
                'cogs' => $cogs,
                'commission' => $commission,
                'profit' => $goods - $discount - $cogs - $commission,
            ];
        })->values();

        // Sắp xếp: ngày tăng dần, còn lại theo doanh thu giảm dần.
        $rows = $this->groupBy === 'day'
            ? $rows->sortBy('label')->values()
            : $rows->sortByDesc('revenue')->values();

        return [
            'mode' => $this->groupBy,
            'rows' => $rows->all(),
        ];
    }

    public function export()
    {
        if (!auth()->user()?->hasPermission('reports')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xuất báo cáo!', type: 'error');
            return;
        }
        $bd = $this->breakdown();

        // Cột xuất: giữ thứ tự gốc, chỉ lấy cột được chọn (mặc định tất cả).
        $all = $this->columnsFor($bd['mode']);
        $selected = !empty($this->visibleColumns)
            ? array_intersect_key($all, array_flip($this->visibleColumns))
            : $all;
        if (empty($selected)) {
            $selected = $all;
        }

        $fileName = 'bao-cao-ban-hang_' . $this->fromDate . '_' . $this->toDate . '.xlsx';
        return Excel::download(new SalesReportExport($bd['rows'], $selected, $this->summaryHeaderLines()), $fileName);
    }

    /** Các dòng tổng quan ở đầu file Excel. */
    protected function summaryHeaderLines(): array
    {
        $s = $this->summary();
        return [
            ['BÁO CÁO BÁN HÀNG', $this->fromDate . ' → ' . $this->toDate],
            ['Số đơn', $s['orders']],
            ['Tiền hàng', $s['goods']],
            ['Giảm giá', $s['discount']],
            ['Doanh thu (khách trả)', $s['revenue']],
            ['Giá vốn', $s['cogs']],
            ['Hoa hồng', $s['commission']],
            ['Lợi nhuận tạm tính', $s['profit']],
        ];
    }

    /** URL trang chi tiết theo ngày (giữ bộ lọc hiện tại). */
    public function dayDetailUrl(string $date): string
    {
        return route('reports.sales.day', array_filter([
            'date' => $date,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'branch' => $this->branch,
            'sellerId' => $this->sellerId,
            'channel' => $this->channel,
        ], fn ($v) => $v !== '' && $v !== null));
    }

    public function render()
    {
        return view('livewire.report.sales-report', [
            'summary' => $this->summary(),
            'breakdown' => $this->breakdown(),
            'branches' => Branch::active(),
            'staff' => User::orderBy('name')->get(['id', 'name']),
            'channels' => array_column(PosTerminal::SALES_CHANNELS, 'name'),
        ])->layout('layouts.app');
    }
}
