<?php

namespace App\Livewire\Dashboard;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Traits\HasPermissions;

class DashboardIndex extends Component
{
    use HasPermissions;

    // Time range for the Top 10 products chart: today | 7d | 30d | 90d | year | all
    public string $topProductsRange = '30d';

    protected function getModuleKey(): string
    {
        return 'dashboard';
    }

    public function setTopProductsRange(string $range): void
    {
        $valid = ['today', '7d', '30d', '90d', 'year', 'all'];
        if (in_array($range, $valid, true)) {
            $this->topProductsRange = $range;
        }
    }

    protected function getTopProductsDateBounds(): ?Carbon
    {
        return match ($this->topProductsRange) {
            'today' => Carbon::today(),
            '7d'    => Carbon::now()->subDays(7),
            '30d'   => Carbon::now()->subDays(30),
            '90d'   => Carbon::now()->subDays(90),
            'year'  => Carbon::now()->startOfYear(),
            default => null, // 'all'
        };
    }

    public function getTopProducts(): array
    {
        $since = $this->getTopProductsDateBounds();

        $rows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->when($since, fn($q) => $q->where('invoices.created_at', '>=', $since))
            ->select(
                'invoice_items.product_id',
                'invoice_items.sku',
                'invoice_items.product_name',
                DB::raw('SUM(invoice_items.quantity) AS total_qty'),
                // final_price đã là thành tiền cả dòng (đơn giá × SL) -> KHÔNG nhân SL lần nữa
                DB::raw('SUM(invoice_items.final_price) AS total_revenue')
            )
            ->groupBy('invoice_items.product_id', 'invoice_items.sku', 'invoice_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return $rows->map(fn($r) => [
            'product_id'    => $r->product_id,
            'sku'           => $r->sku ?? '—',
            'name'          => $r->product_name ?? 'Sản phẩm đã xoá',
            'total_qty'     => (int) $r->total_qty,
            'total_revenue' => (int) $r->total_revenue,
        ])->all();
    }
    public function getStats()
    {
        $today = Carbon::today();
        $invoiceToday = fn() => Invoice::whereDate('created_at', $today);

        return [
            'revenue_today'    => $invoiceToday()->whereNotIn('status', ['Cancelled', 'Returned'])->sum('final_amount'),
            // Chi tiết số đơn hôm nay
            'orders_today'     => $invoiceToday()->count(),                                   // Tổng hóa đơn
            'orders_completed' => $invoiceToday()->whereNotIn('status', ['Cancelled', 'Returned'])->count(), // Hoàn thành
            'orders_returned'  => $invoiceToday()->where('status', 'Returned')->count(),      // Trả hàng
            'orders_cancelled' => $invoiceToday()->where('status', 'Cancelled')->count(),     // Hủy
            'orders_edited'    => $invoiceToday()->whereNotIn('status', ['Cancelled', 'Returned'])
                                    ->whereColumn('updated_at', '>', 'created_at')->count(),  // Đã sửa
            'total_customers'  => Customer::count(),
            'low_stock_count'  => Product::whereRaw('stock_quantity < min_stock')->count(),
        ];
    }

    public function getRecentActivity()
    {
        return Invoice::with(['customer', 'user'])
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getChartData()
    {
        $end   = Carbon::today();
        $start = $end->copy()->subDays(6); // 7 days inclusive

        $totalsByDay = Invoice::query()
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, SUM(final_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Carbon dayOfWeek: 0=Sun, 1=Mon, ..., 6=Sat
        $vnDays = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $date    = $start->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');
            $data[]  = [
                'day'  => $vnDays[$date->dayOfWeek],
                'date' => $date->format('d/m'),
                'val'  => (int) ($totalsByDay[$dateKey] ?? 0),
            ];
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-index', [
            'stats' => $this->getStats(),
            'activities' => $this->getRecentActivity(),
            'chartData' => $this->getChartData(),
            'topProducts' => $this->getTopProducts(),
        ])->layout('layouts.app');
    }
}
