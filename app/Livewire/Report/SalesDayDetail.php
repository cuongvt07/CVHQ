<?php

namespace App\Livewire\Report;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Branch;
use App\Exports\SalesReportExport;
use App\Traits\HasPermissions;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SalesDayDetail extends Component
{
    use HasPermissions;

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public string $date = '';
    // Bộ lọc mang theo từ trang báo cáo (để chi tiết khớp ngữ cảnh + dựng link Quay lại).
    public string $fromDate = '';
    public string $toDate = '';
    public string $branch = '';
    public string $sellerId = '';
    public string $channel = '';

    public array $exportColumns = [];

    protected $queryString = [
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'branch' => ['except' => ''],
        'sellerId' => ['except' => ''],
        'channel' => ['except' => ''],
    ];

    public function mount(string $date): void
    {
        abort_unless(auth()->user()?->hasPermission('reports'), 403);
        $this->date = $date;
        if (empty($this->exportColumns)) {
            $this->exportColumns = array_keys($this->columns());
        }
    }

    public function columns(): array
    {
        return [
            'code' => 'Mã đơn',
            'time' => 'Giờ',
            'customer' => 'Khách hàng',
            'seller' => 'NV bán',
            'channel' => 'Kênh',
            'branch' => 'Chi nhánh',
            'qty' => 'SL',
            'goods' => 'Tiền hàng',
            'discount' => 'Giảm giá',
            'revenue' => 'Khách trả',
            'commission' => 'Hoa hồng',
            'profit' => 'Lợi nhuận tạm tính',
        ];
    }

    protected function invoiceQuery()
    {
        return Invoice::query()
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereDate('created_at', $this->date)
            ->when($this->branch !== '', fn ($q) => $q->where('branch', $this->branch))
            ->when($this->sellerId !== '', fn ($q) => $q->where('user_id', $this->sellerId))
            ->when($this->channel !== '', fn ($q) => $q->where('sales_channel', $this->channel));
    }

    public function rows(): array
    {
        $invoices = $this->invoiceQuery()
            ->with('customer:id,full_name')
            ->orderBy('created_at')
            ->get();

        $agg = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereNull('invoices.deleted_at')
            ->whereNull('invoice_items.deleted_at')
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->whereDate('invoices.created_at', $this->date)
            ->when($this->branch !== '', fn ($q) => $q->where('invoices.branch', $this->branch))
            ->when($this->sellerId !== '', fn ($q) => $q->where('invoices.user_id', $this->sellerId))
            ->when($this->channel !== '', fn ($q) => $q->where('invoices.sales_channel', $this->channel))
            ->selectRaw('invoice_items.invoice_id AS iid,
                SUM(invoice_items.quantity) AS qty,
                SUM(COALESCE(products.cost_price,0) * invoice_items.quantity) AS cogs')
            ->groupBy('invoice_items.invoice_id')
            ->get()
            ->keyBy('iid');

        return $invoices->map(function ($inv) use ($agg) {
            $a = $agg->get($inv->id);
            $qty = (int) ($a->qty ?? 0);
            $cogs = (int) ($a->cogs ?? 0);
            $goods = (int) $inv->total_amount;
            $discount = (int) $inv->discount_amount;
            $commission = (int) $inv->total_commission;
            return [
                'code' => $inv->invoice_code,
                'time' => optional($inv->created_at)->format('H:i'),
                'customer' => $inv->customer?->full_name ?: 'Khách lẻ',
                'seller' => $inv->seller_name ?: '—',
                'channel' => $inv->sales_channel ?: '—',
                'branch' => Branch::nameOf($inv->branch),
                'qty' => $qty,
                'goods' => $goods,
                'discount' => $discount,
                'revenue' => (int) $inv->final_amount,
                'commission' => $commission,
                'profit' => $goods - $discount - $cogs - $commission,
            ];
        })->all();
    }

    /** Link quay lại trang báo cáo (giữ bộ lọc + chế độ ngày). */
    public function backUrl(): string
    {
        return route('reports.sales', array_filter([
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'branch' => $this->branch,
            'sellerId' => $this->sellerId,
            'channel' => $this->channel,
            'groupBy' => 'day',
        ], fn ($v) => $v !== '' && $v !== null));
    }

    public function export()
    {
        if (!auth()->user()?->hasPermission('reports')) {
            return;
        }
        $all = $this->columns();
        $selected = !empty($this->exportColumns)
            ? array_intersect_key($all, array_flip($this->exportColumns))
            : $all;
        if (empty($selected)) {
            $selected = $all;
        }

        $rows = $this->rows();
        $header = [['CHI TIẾT ĐƠN HÀNG', 'Ngày ' . $this->date], ['Tổng số đơn', count($rows)]];
        return Excel::download(
            new SalesReportExport($rows, $selected, $header),
            'chi-tiet-don-hang_' . $this->date . '.xlsx'
        );
    }

    public function render()
    {
        $rows = $this->rows();
        return view('livewire.report.sales-day-detail', [
            'rows' => $rows,
            'columns' => $this->columns(),
            'totalRevenue' => array_sum(array_column($rows, 'revenue')),
            'totalProfit' => array_sum(array_column($rows, 'profit')),
            'totalOrders' => count($rows),
            'dateLabel' => Carbon::parse($this->date)->format('d/m/Y'),
        ])->layout('layouts.app');
    }
}
