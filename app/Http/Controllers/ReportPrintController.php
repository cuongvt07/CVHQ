<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportPrintController extends Controller
{
    /**
     * Gộp doanh thu theo từng THÁNG trong khoảng [fromMonth, toMonth] (Y-m).
     * Bỏ hóa đơn Hủy / Trả hàng. Dùng chung cho trang xem (Livewire) và trang in.
     */
    public static function buildRows(string $fromMonth, string $toMonth, string $branch = 'all'): array
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $fromMonth)->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfYear();
        }
        try {
            $end = Carbon::createFromFormat('Y-m', $toMonth)->endOfMonth();
        } catch (\Throwable $e) {
            $end = now()->endOfMonth();
        }
        if ($start->gt($end)) {
            $tmp   = $start->copy()->startOfMonth();
            $start = $end->copy()->startOfMonth();
            $end   = $tmp->endOfMonth();
        }

        $agg = Invoice::whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branch !== 'all', fn ($q) => $q->where('branch', $branch))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym,
                         COUNT(*) as cnt,
                         SUM(total_amount) as goods,
                         SUM(discount_amount) as discount,
                         SUM(extra_fee) as extra,
                         SUM(final_amount) as total")
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $rows = [];
        $cur = $start->copy()->startOfMonth();
        $guard = 0;
        while ($cur->lte($end) && $guard < 600) {
            $a = $agg->get($cur->format('Y-m'));
            $rows[] = [
                'label'    => $cur->copy()->startOfMonth()->format('d-m-Y') . ' → ' . $cur->copy()->endOfMonth()->format('d-m-Y'),
                'cnt'      => (int) ($a->cnt ?? 0),
                'goods'    => (int) ($a->goods ?? 0),
                'discount' => (int) ($a->discount ?? 0),
                'extra'    => (int) ($a->extra ?? 0),
                'total'    => (int) ($a->total ?? 0),
            ];
            $cur->addMonth();
            $guard++;
        }

        $sum = fn ($k) => array_sum(array_column($rows, $k));

        return [
            'from'      => $start,
            'to'        => $end,
            'fromMonth' => $start->format('Y-m'),
            'toMonth'   => $end->format('Y-m'),
            'rows'      => $rows,
            'totals'    => [
                'cnt'      => $sum('cnt'),
                'goods'    => $sum('goods'),
                'discount' => $sum('discount'),
                'extra'    => $sum('extra'),
                'total'    => $sum('total'),
            ],
            'company'    => SystemSetting::get('shop_name') ?: SystemSetting::get('app_name', 'CÔNG TY'),
            'address'    => self::companyAddress($branch),
            'branch'     => $branch,
            'branchName' => $branch === 'all' ? 'Tất cả chi nhánh' : \App\Models\Branch::nameOf($branch),
        ];
    }

    /** Địa chỉ hiển thị: theo chi nhánh đang lọc (Cấu hình chung); "Tất cả" -> lấy HN (trụ sở) hoặc SG. */
    private static function companyAddress(string $branch): string
    {
        if ($branch !== 'all') {
            $addr = (string) SystemSetting::get('shop_' . $branch . '_address', '');
            if ($addr !== '') return $addr;
        }
        return (string) (SystemSetting::get('shop_hn_address')
            ?: SystemSetting::get('shop_sg_address') ?: '');
    }

    /** Trang IN báo cáo doanh thu theo tháng (HTML độc lập, không layout app). */
    public function monthlyRevenue(Request $request)
    {
        abort_unless(auth()->check(), 403);
        $from   = (string) $request->query('from', now()->startOfYear()->format('Y-m'));
        $to     = (string) $request->query('to', now()->format('Y-m'));
        $branch = (string) $request->query('branch', 'all');

        return view('reports.revenue-print', self::buildRows($from, $to, $branch));
    }

    // ── Mốc 1 tháng (Y-m) ────────────────────────────────────────────────────
    private static function monthBounds(string $month): array
    {
        try {
            $s = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $e) {
            $s = now()->startOfMonth();
        }
        return [$s, $s->copy()->endOfMonth(), $s->format('Y-m')];
    }

    private static function head(string $branch, string $ym): array
    {
        return [
            'company'    => SystemSetting::get('shop_name') ?: SystemSetting::get('app_name', 'CÔNG TY'),
            'address'    => self::companyAddress($branch),
            'branch'     => $branch,
            'branchName' => $branch === 'all' ? 'Tất cả chi nhánh' : \App\Models\Branch::nameOf($branch),
            'month'      => $ym,
            'monthLabel' => Carbon::createFromFormat('Y-m', $ym)->format('m/Y'),
        ];
    }

    // ── Báo cáo sản phẩm: bán chạy nhất / không bán được ─────────────────────
    public static function buildProducts(string $month, string $branch, string $mode, int $limit): array
    {
        [$start, $end, $ym] = self::monthBounds($month);
        $mode  = in_array($mode, ['best', 'worst'], true) ? $mode : 'best';
        $limit = max(1, min(100, $limit));

        // Tổng bán theo sản phẩm trong kỳ (lọc theo chi nhánh / trạng thái HĐ).
        $sold = \App\Models\InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereNotIn('invoices.status', ['Cancelled', 'Returned'])
            ->whereBetween('invoices.created_at', [$start, $end])
            ->when($branch !== 'all', fn ($q) => $q->where('invoices.branch', $branch))
            ->selectRaw('invoice_items.product_id,
                         MAX(invoice_items.sku) as sku,
                         MAX(invoice_items.product_name) as name,
                         SUM(invoice_items.quantity) as qty,
                         SUM(invoice_items.final_price) as revenue')
            ->groupBy('invoice_items.product_id')
            ->get();

        if ($mode === 'best') {
            $top = $sold->sortByDesc('qty')->take($limit)->values();
            $stockMap = \App\Models\Product::whereIn('id', $top->pluck('product_id')->filter()->all())
                ->pluck('stock_quantity', 'id');
            $rows = $top->map(fn ($r) => [
                'sku'     => $r->sku,
                'name'    => $r->name,
                'qty'     => (int) $r->qty,
                'revenue' => (int) $r->revenue,
                'stock'   => (int) ($stockMap[$r->product_id] ?? 0),
            ])->all();
        } else {
            // Không bán được: sản phẩm KHÔNG nằm trong tập đã bán (phạm vi chi nhánh), tồn nhiều xuống ít.
            $soldIds = $sold->pluck('product_id')->filter()->all();
            $rows = \App\Models\Product::query()
                ->when(!empty($soldIds), fn ($q) => $q->whereNotIn('id', $soldIds))
                ->orderByDesc('stock_quantity')
                ->take($limit)
                ->get(['id', 'sku', 'base_name', 'name', 'stock_quantity'])
                ->map(fn ($p) => [
                    'sku'     => $p->sku,
                    'name'    => $p->base_name ?: $p->name,
                    'qty'     => 0,
                    'revenue' => 0,
                    'stock'   => (int) $p->stock_quantity,
                ])->all();
        }

        $sum = fn ($k) => array_sum(array_column($rows, $k));

        return self::head($branch, $ym) + [
            'mode'      => $mode,
            'modeLabel' => $mode === 'best' ? 'Bán chạy nhất' : 'Không bán được',
            'limit'     => $limit,
            'rows'      => $rows,
            'totals'    => ['qty' => $sum('qty'), 'revenue' => $sum('revenue'), 'stock' => $sum('stock')],
        ];
    }

    public function products(Request $request)
    {
        abort_unless(auth()->check(), 403);
        $month  = (string) $request->query('month', now()->format('Y-m'));
        $branch = (string) $request->query('branch', 'all');
        $mode   = (string) $request->query('mode', 'best');
        $limit  = (int) $request->query('limit', 15);

        return view('reports.product-print', self::buildProducts($month, $branch, $mode, $limit));
    }

    // ── Báo cáo hoa hồng nhân viên (chỉ NV nhận hoa hồng) ─────────────────────
    public static function buildCommission(string $month, string $branch): array
    {
        [$start, $end, $ym] = self::monthBounds($month);

        $agg = \App\Models\Invoice::query()
            ->whereNotIn('status', ['Cancelled', 'Returned'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branch !== 'all', fn ($q) => $q->where('branch', $branch))
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as cnt, SUM(final_amount) as sales, SUM(total_commission) as commission')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Toàn bộ NV được nhận hoa hồng (kể cả 0 đơn trong kỳ).
        $users = \App\Models\User::where('can_receive_commission', true)
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        $rows = [];
        foreach ($users as $u) {
            $a = $agg->get($u->id);
            $rows[] = [
                'name'       => $u->name . ($u->is_active ? '' : ' (đã nghỉ)'),
                'cnt'        => (int) ($a->cnt ?? 0),
                'sales'      => (int) ($a->sales ?? 0),
                'commission' => (int) ($a->commission ?? 0),
            ];
        }
        usort($rows, fn ($x, $y) => $y['commission'] <=> $x['commission']);

        $sum = fn ($k) => array_sum(array_column($rows, $k));

        return self::head($branch, $ym) + [
            'rows'   => $rows,
            'totals' => ['cnt' => $sum('cnt'), 'sales' => $sum('sales'), 'commission' => $sum('commission')],
        ];
    }

    public function commission(Request $request)
    {
        abort_unless(auth()->check(), 403);
        $month  = (string) $request->query('month', now()->format('Y-m'));
        $branch = (string) $request->query('branch', 'all');

        return view('reports.commission-print', self::buildCommission($month, $branch));
    }
}
