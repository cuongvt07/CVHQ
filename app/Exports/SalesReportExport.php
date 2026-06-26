<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

/**
 * Xuất báo cáo bán hàng — chỉ các cột được chọn (mặc định tất cả cột hiển thị),
 * kèm khối tổng quan ở đầu file.
 */
class SalesReportExport implements FromArray
{
    public function __construct(
        protected array $rows,
        protected array $columns,   // [key => heading] đã lọc theo lựa chọn
        protected array $summary,
    ) {
    }

    public function array(): array
    {
        $out = [];
        $out[] = ['BÁO CÁO BÁN HÀNG'];
        $out[] = ['Số đơn', $this->summary['orders'] ?? 0];
        $out[] = ['Tiền hàng', $this->summary['goods'] ?? 0];
        $out[] = ['Giảm giá', $this->summary['discount'] ?? 0];
        $out[] = ['Doanh thu (khách trả)', $this->summary['revenue'] ?? 0];
        $out[] = ['Giá vốn', $this->summary['cogs'] ?? 0];
        $out[] = ['Hoa hồng', $this->summary['commission'] ?? 0];
        $out[] = ['Lợi nhuận tạm tính', $this->summary['profit'] ?? 0];
        $out[] = []; // dòng trống

        // Header bảng + dữ liệu (chỉ cột được chọn)
        $keys = array_keys($this->columns);
        $out[] = array_values($this->columns);
        foreach ($this->rows as $row) {
            $line = [];
            foreach ($keys as $k) {
                $line[] = $row[$k] ?? '';
            }
            $out[] = $line;
        }

        return $out;
    }
}
