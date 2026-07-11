<?php

namespace App\Livewire\Report;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Livewire\Pos\PosTerminal;
use App\Exports\DashboardExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Traits\HasPermissions;

class OverviewReport extends Component
{
    use HasPermissions;

    public string $fromDate = '';
    public string $toDate = '';
    public string $compareWith = '7d'; // 1d|7d|28d|90d|prevmonth|prevyear

    protected $queryString = [
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'compareWith' => ['except' => '7d'],
    ];

    protected function getModuleKey(): string
    {
        return 'reports';
    }

    public function mount(): void
    {
        if ($this->fromDate === '') {
            $this->fromDate = now()->startOfMonth()->toDateString();
        }
        if ($this->toDate === '') {
            $this->toDate = now()->toDateString();
        }
    }

    /** Đặt nhanh khoảng thời gian. */
    public function setPreset(string $preset): void
    {
        $now = now();
        [$f, $t] = match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()],
            '90d' => [$now->copy()->subDays(89)->startOfDay(), $now->copy()],
            'lastmonth' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'weektodate' => [$now->copy()->startOfWeek(), $now->copy()],
            'monthtodate' => [$now->copy()->startOfMonth(), $now->copy()],
            default => [$now->copy()->startOfMonth(), $now->copy()],
        };
        $this->fromDate = $f->toDateString();
        $this->toDate = $t->toDateString();
    }

    public function setCompare(string $c): void
    {
        if (in_array($c, ['1d', '7d', '28d', '90d', 'prevmonth', 'prevyear'], true)) {
            $this->compareWith = $c;
        }
    }

    /** [from, to] của kỳ hiện tại. */
    protected function bounds(): array
    {
        return [Carbon::parse($this->fromDate)->startOfDay(), Carbon::parse($this->toDate)->endOfDay()];
    }

    /** Lùi 1 mốc thời gian theo lựa chọn "So sánh với". */
    protected function shiftBack(Carbon $d): Carbon
    {
        return match ($this->compareWith) {
            '1d' => $d->copy()->subDay(),
            '28d' => $d->copy()->subDays(28),
            '90d' => $d->copy()->subDays(90),
            'prevmonth' => $d->copy()->subMonthNoOverflow(),
            'prevyear' => $d->copy()->subYear(),
            default => $d->copy()->subDays(7),
        };
    }

    /** [from, to] của kỳ so sánh. */
    protected function prevBounds(): array
    {
        [$from, $to] = $this->bounds();
        return [$this->shiftBack($from)->startOfDay(), $this->shiftBack($to)->endOfDay()];
    }

    protected function invoiceQuery($from, $to)
    {
        return Invoice::query()
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereBetween('created_at', [$from, $to]);
    }

    protected function itemQuery($from, $to)
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereNull('invoices.deleted_at')
            ->whereNull('invoice_items.deleted_at')
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->whereBetween('invoices.created_at', [$from, $to]);
    }

    protected static function pct($cur, $prev): ?float
    {
        if ($prev == 0) {
            return $cur == 0 ? 0.0 : null; // null = không có gốc so sánh
        }
        return round(($cur - $prev) / $prev * 100, 2);
    }

    /** Tổng hợp doanh số/thu/lợi nhuận/đơn/SL cho 1 kỳ. */
    protected function aggregate($from, $to): array
    {
        $inv = $this->invoiceQuery($from, $to)->selectRaw('
            COUNT(*) AS orders,
            COALESCE(SUM(total_amount),0) AS goods,
            COALESCE(SUM(discount_amount),0) AS discount,
            COALESCE(SUM(final_amount),0) AS revenue,
            COALESCE(SUM(total_commission),0) AS commission
        ')->first();

        $it = $this->itemQuery($from, $to)->selectRaw('
            COALESCE(SUM(invoice_items.quantity),0) AS qty,
            COALESCE(SUM(COALESCE(products.cost_price,0)*invoice_items.quantity),0) AS cogs
        ')->first();

        $goods = (int) $inv->goods;
        $discount = (int) $inv->discount;
        $cogs = (int) $it->cogs;
        $commission = (int) $inv->commission;
        $orders = (int) $inv->orders;

        return [
            'orders' => $orders,
            'goods' => $goods,
            'discount' => $discount,
            'revenue' => (int) $inv->revenue,
            'commission' => $commission,
            'qty' => (int) $it->qty,
            'cogs' => $cogs,
            'profit' => $goods - $discount - $cogs - $commission,
            'aov' => $orders > 0 ? intdiv((int) $inv->revenue, $orders) : 0, // GTTB
        ];
    }

    public function render()
    {
        [$from, $to] = $this->bounds();
        [$pf, $pt] = $this->prevBounds();

        $cur = $this->aggregate($from, $to);
        $prev = $this->aggregate($pf, $pt);

        return view('livewire.report.overview-report', [
            'cur' => $cur,
            'kpi' => $this->kpi($from, $to, $pf, $pt, $cur, $prev),
            'metrics' => $this->metrics($cur, $prev),
            'revenueSplit' => $this->revenueSplit($from, $to),
            'lineChart' => $this->lineChart($from, $to, $pf, $pt),
            'today' => $this->today(),
            'orderStats' => $this->orderStats($from, $to),
            'recentTx' => $this->recentTransactions(),
            'branches' => $this->byBranch($from, $to),
            'sources' => $this->bySource($from, $to),
            'products' => $this->topProducts($from, $to),
            'staff' => $this->byStaff($from, $to),
        ])->layout('layouts.app');
    }

    public function export()
    {
        if (!auth()->user()?->hasPermission('reports')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xuất báo cáo!', type: 'error');
            return;
        }

        [$from, $to] = $this->bounds();
        [$pf, $pt] = $this->prevBounds();
        $cur = $this->aggregate($from, $to);
        $prev = $this->aggregate($pf, $pt);

        return Excel::download(new DashboardExport(
            $this->fromDate,
            $this->toDate,
            $this->kpi($from, $to, $pf, $pt, $cur, $prev),
            $this->metrics($cur, $prev),
            $this->revenueSplit($from, $to),
            $this->byBranch($from, $to),
            $this->bySource($from, $to),
            $this->topProducts($from, $to),
            $this->byStaff($from, $to),
        ), 'bao-cao-tong-quan_' . $this->fromDate . '_' . $this->toDate . '.xlsx');
    }

    protected function kpi($from, $to, $pf, $pt, $cur, $prev): array
    {
        // Hàng hoàn (đơn trả) trong kỳ.
        $ret = Invoice::query()->where('status', 'Returned')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) AS orders, COALESCE(SUM(final_amount),0) AS amount')->first();
        $retPrev = Invoice::query()->where('status', 'Returned')
            ->whereBetween('created_at', [$pf, $pt])
            ->selectRaw('COALESCE(SUM(final_amount),0) AS amount')->first();
        $retQty = (int) InvoiceItem::query()->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.status', 'Returned')->whereBetween('invoices.created_at', [$from, $to])
            ->sum('invoice_items.quantity');

        // Có thể bán (tồn kho hiện tại).
        $stock = Product::query()->selectRaw('
            COALESCE(SUM(stock_quantity),0) AS qty,
            COALESCE(SUM(cost_price*stock_quantity),0) AS cost_value,
            COALESCE(SUM(sale_price*stock_quantity),0) AS sale_value
        ')->first();

        return [
            'chot' => [
                'amount' => $cur['revenue'], 'qty' => $cur['qty'],
                'amount_pct' => self::pct($cur['revenue'], $prev['revenue']),
                'qty_pct' => self::pct($cur['qty'], $prev['qty']),
            ],
            'hoan' => [
                'amount' => (int) $ret->amount, 'qty' => $retQty,
                'amount_pct' => self::pct((int) $ret->amount, (int) $retPrev->amount),
            ],
            'ton' => [
                'qty' => (int) $stock->qty,
                'cost_value' => (int) $stock->cost_value,
                'sale_value' => (int) $stock->sale_value,
            ],
        ];
    }

    protected function metrics($cur, $prev): array
    {
        $def = [
            ['key' => 'goods', 'label' => 'Doanh số'],
            ['key' => 'revenue', 'label' => 'Doanh thu'],
            ['key' => 'profit', 'label' => 'Lợi nhuận'],
            ['key' => 'orders', 'label' => 'Đơn chốt'],
            ['key' => 'aov', 'label' => 'GTTB'],
            ['key' => 'qty', 'label' => 'SL sản phẩm'],
        ];
        return array_map(fn ($m) => [
            'label' => $m['label'],
            'value' => $cur[$m['key']],
            'pct' => self::pct($cur[$m['key']], $prev[$m['key']]),
        ], $def);
    }

    protected function revenueSplit($from, $to): array
    {
        $rows = $this->invoiceQuery($from, $to)
            ->selectRaw("CASE WHEN sales_channel = 'Trực tiếp' THEN 'quay' ELSE 'online' END AS grp,
                COUNT(*) AS orders, COALESCE(SUM(final_amount),0) AS revenue")
            ->groupBy('grp')->get()->keyBy('grp');

        $online = $rows->get('online');
        $quay = $rows->get('quay');
        $totalRev = (int) ($online->revenue ?? 0) + (int) ($quay->revenue ?? 0);
        $totalOrd = (int) ($online->orders ?? 0) + (int) ($quay->orders ?? 0);

        return [
            'total' => ['revenue' => $totalRev, 'orders' => $totalOrd],
            'online' => ['revenue' => (int) ($online->revenue ?? 0), 'orders' => (int) ($online->orders ?? 0)],
            'quay' => ['revenue' => (int) ($quay->revenue ?? 0), 'orders' => (int) ($quay->orders ?? 0)],
        ];
    }

    protected function lineChart($from, $to, $pf, $pt): array
    {
        // Doanh thu + số đơn theo ngày (kỳ hiện tại + kỳ so sánh).
        $curByDay = $this->invoiceQuery($from, $to)->selectRaw('DATE(created_at) d, SUM(final_amount) t, COUNT(*) o')
            ->groupBy('d')->get()->keyBy('d');
        $prevByDay = $this->invoiceQuery($pf, $pt)->selectRaw('DATE(created_at) d, SUM(final_amount) t')
            ->groupBy('d')->pluck('t', 'd');

        $days = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $days = max(1, min($days, 120)); // giới hạn an toàn

        $cur = [];
        $prev = [];
        $labels = [];
        $points = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i);
            $pd = $this->shiftBack($d);
            $revKey = $d->format('Y-m-d');
            $rev = (int) ($curByDay[$revKey]->t ?? 0);
            $ord = (int) ($curByDay[$revKey]->o ?? 0);
            $prevRev = (int) ($prevByDay[$pd->format('Y-m-d')] ?? 0);

            $cur[] = $rev;
            $prev[] = $prevRev;
            $labels[] = $d->format('d/m');
            $points[] = [
                'date' => $d->format('d/m/Y'),
                'cmp' => $pd->format('d/m/Y'),
                'rev' => $rev,
                'prev' => $prevRev,
                'orders' => $ord,
                'trend' => self::pct($rev, $prevRev),
            ];
        }
        return ['labels' => $labels, 'cur' => $cur, 'prev' => $prev, 'points' => $points];
    }

    protected function today(): array
    {
        $today = Carbon::today();
        $base = fn () => Invoice::whereDate('created_at', $today);

        $valid = (clone $base())->whereNotIn('status', ['Cancelled', 'Returned']);
        $revenue = (int) (clone $valid)->sum('final_amount');
        $orders = (int) (clone $valid)->count();

        // Doanh thu theo giờ (0..23).
        $byHour = (clone $valid)->selectRaw('HOUR(created_at) h, SUM(final_amount) t')->groupBy('h')->pluck('t', 'h');
        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $hours[] = (int) ($byHour[$h] ?? 0);
        }

        // Online vs quầy hôm nay.
        $split = (clone $valid)->selectRaw("CASE WHEN sales_channel='Trực tiếp' THEN 'quay' ELSE 'online' END grp,
            COUNT(*) orders, COALESCE(SUM(final_amount),0) rev")->groupBy('grp')->get()->keyBy('grp');

        $qty = (int) InvoiceItem::query()->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereDate('invoices.created_at', $today)
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->sum('invoice_items.quantity');

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'hours' => $hours,
            'online' => ['rev' => (int) ($split['online']->rev ?? 0), 'orders' => (int) ($split['online']->orders ?? 0)],
            'quay' => ['rev' => (int) ($split['quay']->rev ?? 0), 'orders' => (int) ($split['quay']->orders ?? 0)],
            'created' => (int) (clone $base())->count(),
            'cancelled' => (int) (clone $base())->where('status', 'Cancelled')->count(),
            'chot' => $orders,
            'deleted' => (int) Invoice::onlyTrashed()->whereDate('created_at', $today)->count(),
            'qty' => $qty,
            'customers' => (int) (clone $valid)->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id'),
        ];
    }

    // Chi tiết số đơn trong kỳ đã chọn: tổng / hoàn thành / trả hàng / sửa / hủy.
    protected function orderStats($from, $to): array
    {
        $base = fn () => Invoice::query()->whereBetween('created_at', [$from, $to]);
        $total     = (int) $base()->count();
        $returned  = (int) $base()->where('status', 'Returned')->count();
        $cancelled = (int) $base()->where('status', 'Cancelled')->count();
        $completed = (int) $base()->whereNotIn('status', ['Returned', 'Cancelled'])->count();
        // "Sửa": số hóa đơn từng bị chỉnh sửa (có nhật ký 'updated') trong kỳ.
        $edited = (int) \App\Models\ActivityLog::where('model_type', Invoice::class)
            ->where('action', 'updated')
            ->whereBetween('created_at', [$from, $to])
            ->distinct('model_id')->count('model_id');

        return compact('total', 'completed', 'returned', 'edited', 'cancelled');
    }

    // Giao dịch gần đây: hóa đơn mới nhất (còn hiệu lực) kèm người tạo + link chi tiết.
    protected function recentTransactions(): array
    {
        return Invoice::with(['customer', 'user'])
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->latest('created_at')->limit(8)->get()
            ->map(fn ($inv) => [
                'id'       => $inv->id,
                'code'     => $inv->invoice_code,
                'customer' => $inv->customer?->full_name ?? 'Khách lẻ',
                'seller'   => $inv->seller_name ?: ($inv->user?->name ?? '—'),
                'amount'   => (int) $inv->final_amount,
                'time'     => optional($inv->created_at)->format('d/m H:i'),
                'channel'  => $inv->sales_channel,
            ])->all();
    }

    protected function byBranch($from, $to): array
    {
        $inv = $this->invoiceQuery($from, $to)->selectRaw("
            COALESCE(NULLIF(branch,''),'—') AS b,
            COUNT(*) orders, COALESCE(SUM(final_amount),0) revenue,
            COALESCE(SUM(total_amount),0) goods, COALESCE(SUM(discount_amount),0) discount")
            ->groupBy('b')->get()->keyBy('b');

        $it = $this->itemQuery($from, $to)->selectRaw("
            COALESCE(NULLIF(invoices.branch,''),'—') AS b, SUM(invoice_items.quantity) qty")
            ->groupBy('b')->get()->keyBy('b');

        return $inv->map(function ($r, $b) use ($it) {
            $orders = (int) $r->orders;
            $revenue = (int) $r->revenue;
            return [
                'label' => Branch::nameOf((string) $b) ?: (string) $b,
                'revenue' => $revenue,
                'goods' => (int) $r->goods,
                'discount' => (int) $r->discount,
                'orders' => $orders,
                'qty' => (int) ($it[$b]->qty ?? 0),
                'aov' => $orders > 0 ? intdiv($revenue, $orders) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    protected function bySource($from, $to): array
    {
        $inv = $this->invoiceQuery($from, $to)->selectRaw("
            COALESCE(NULLIF(sales_channel,''),'Chưa có nguồn') AS c,
            COUNT(*) orders, COALESCE(SUM(final_amount),0) revenue,
            COALESCE(SUM(total_amount),0) goods, COALESCE(SUM(discount_amount),0) discount,
            COALESCE(SUM(total_commission),0) commission")
            ->groupBy('c')->get()->keyBy('c');

        $it = $this->itemQuery($from, $to)->selectRaw("
            COALESCE(NULLIF(invoices.sales_channel,''),'Chưa có nguồn') AS c,
            SUM(invoice_items.quantity) qty,
            SUM(COALESCE(products.cost_price,0)*invoice_items.quantity) cogs")
            ->groupBy('c')->get()->keyBy('c');

        return $inv->map(function ($r, $c) use ($it) {
            $orders = (int) $r->orders;
            $revenue = (int) $r->revenue;
            $goods = (int) $r->goods;
            $discount = (int) $r->discount;
            $cogs = (int) ($it[$c]->cogs ?? 0);
            $commission = (int) $r->commission;
            return [
                'label' => (string) $c,
                'revenue' => $revenue,
                'profit' => $goods - $discount - $cogs - $commission,
                'goods' => $goods,
                'discount' => $discount,
                'orders' => $orders,
                'qty' => (int) ($it[$c]->qty ?? 0),
                'aov' => $orders > 0 ? intdiv($revenue, $orders) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    protected function topProducts($from, $to): array
    {
        $rows = $this->itemQuery($from, $to)->selectRaw('
            invoice_items.product_id, invoice_items.sku, invoice_items.product_name,
            SUM(invoice_items.quantity) qty, SUM(invoice_items.final_price) revenue')
            ->groupBy('invoice_items.product_id', 'invoice_items.sku', 'invoice_items.product_name')
            ->orderByDesc('revenue')->limit(10)->get();

        return $rows->map(fn ($r) => [
            'sku' => $r->sku ?? '—',
            'name' => $r->product_name ?? 'Sản phẩm đã xoá',
            'qty' => (int) $r->qty,
            'revenue' => (int) $r->revenue,
        ])->all();
    }

    protected function byStaff($from, $to): array
    {
        // Số đơn tổng (kể cả huỷ/trả) theo NV để tính tỷ lệ chốt.
        $all = Invoice::query()->whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(NULLIF(seller_name,''),'—') s, COUNT(*) total")
            ->groupBy('s')->pluck('total', 's');

        $inv = $this->invoiceQuery($from, $to)->selectRaw("
            COALESCE(NULLIF(seller_name,''),'—') AS s,
            COUNT(*) orders, COALESCE(SUM(final_amount),0) revenue,
            COALESCE(SUM(total_amount),0) goods, COALESCE(SUM(discount_amount),0) discount")
            ->groupBy('s')->get()->keyBy('s');

        return $inv->map(function ($r, $s) use ($all) {
            $orders = (int) $r->orders;
            $total = (int) ($all[$s] ?? $orders);
            return [
                'label' => (string) $s,
                'revenue' => (int) $r->revenue,
                'goods' => (int) $r->goods,
                'discount' => (int) $r->discount,
                'orders' => $orders,
                'rate' => $total > 0 ? round($orders / $total * 100, 1) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();
    }
}
