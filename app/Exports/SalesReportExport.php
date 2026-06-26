<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

/**
 * Xuất Excel cho báo cáo bán hàng (tổng hợp HOẶC chi tiết theo ngày).
 * - $headerLines: các dòng tiêu đề/tổng quan ở đầu file (mảng các mảng).
 * - $columns: [key => heading] đã lọc theo cột người dùng chọn.
 * - $rows: mảng các dòng dữ liệu (mỗi dòng là mảng theo key).
 */
class SalesReportExport implements FromArray
{
    public function __construct(
        protected array $rows,
        protected array $columns,
        protected array $headerLines = [],
    ) {
    }

    public function array(): array
    {
        $out = [];
        foreach ($this->headerLines as $line) {
            $out[] = $line;
        }
        if (!empty($this->headerLines)) {
            $out[] = []; // dòng trống ngăn cách
        }

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
