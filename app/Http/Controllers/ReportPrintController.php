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
    public static function buildRows(string $fromMonth, string $toMonth): array
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
            'company'   => SystemSetting::get('app_name', 'CÔNG TY'),
            'address'   => SystemSetting::get('company_address', ''),
        ];
    }

    /** Trang IN báo cáo doanh thu theo tháng (HTML độc lập, không layout app). */
    public function monthlyRevenue(Request $request)
    {
        abort_unless(auth()->check(), 403);
        $from = (string) $request->query('from', now()->startOfYear()->format('Y-m'));
        $to   = (string) $request->query('to', now()->format('Y-m'));

        return view('reports.revenue-print', self::buildRows($from, $to));
    }
}
