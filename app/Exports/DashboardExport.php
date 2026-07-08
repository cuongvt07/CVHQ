<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Xuất báo cáo Tổng quan (dashboard) ra nhiều sheet. */
class DashboardExport implements WithMultipleSheets
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected array $kpi,
        protected array $metrics,
        protected array $split,
        protected array $branches,
        protected array $sources,
        protected array $products,
        protected array $staff,
    ) {
    }

    public function sheets(): array
    {
        // Sheet 1: Tổng quan (chỉ số).
        $summary = [
            ['Kỳ báo cáo', $this->from . ' → ' . $this->to],
            ['', ''],
            ['HÀNG CHỐT', ''],
            ['Tổng tiền', $this->kpi['chot']['amount']],
            ['Số lượng', $this->kpi['chot']['qty']],
            ['HÀNG HOÀN', ''],
            ['Tổng tiền hoàn', $this->kpi['hoan']['amount']],
            ['Số lượng hoàn', $this->kpi['hoan']['qty']],
            ['CÓ THỂ BÁN (TỒN KHO)', ''],
            ['Số lượng tồn', $this->kpi['ton']['qty']],
            ['Giá vốn tồn', $this->kpi['ton']['cost_value']],
            ['Giá bán tồn', $this->kpi['ton']['sale_value']],
            ['DOANH THU', ''],
            ['Tổng cộng', $this->split['total']['revenue']],
            ['Online', $this->split['online']['revenue']],
            ['Bán tại quầy', $this->split['quay']['revenue']],
            ['CHỈ SỐ', ''],
        ];
        foreach ($this->metrics as $m) {
            $summary[] = [$m['label'], $m['value']];
        }

        return [
            new ArraySheet('Tổng quan', ['Chỉ số', 'Giá trị'], $summary),

            new ArraySheet('Kho hàng',
                ['Kho hàng', 'Doanh thu', 'Doanh số', 'Chiết khấu', 'Đơn chốt', 'SL bán', 'GTTB'],
                array_map(fn ($r) => [$r['label'], $r['revenue'], $r['goods'], $r['discount'], $r['orders'], $r['qty'], $r['aov']], $this->branches)),

            new ArraySheet('Nguồn đơn',
                ['Nguồn đơn', 'Doanh thu', 'Lợi nhuận', 'Doanh số', 'Chiết khấu', 'Đơn chốt', 'SL bán', 'GTTB'],
                array_map(fn ($r) => [$r['label'], $r['revenue'], $r['profit'], $r['goods'], $r['discount'], $r['orders'], $r['qty'], $r['aov']], $this->sources)),

            new ArraySheet('Sản phẩm',
                ['SKU', 'Tên sản phẩm', 'Doanh thu', 'SL bán'],
                array_map(fn ($r) => [$r['sku'], $r['name'], $r['revenue'], $r['qty']], $this->products)),

            new ArraySheet('Nhân viên',
                ['Nhân viên', 'Doanh thu', 'Doanh số', 'Chiết khấu', 'Đơn chốt', 'Tỷ lệ chốt (%)'],
                array_map(fn ($r) => [$r['label'], $r['revenue'], $r['goods'], $r['discount'], $r['orders'], $r['rate']], $this->staff)),
        ];
    }
}
