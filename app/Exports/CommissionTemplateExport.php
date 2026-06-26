<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * File MẪU import Bảng hoa hồng.
 * Bố cục khớp reader (đọc từ dòng 3): dòng 1 tiêu đề bảng, dòng 2 header cột, dòng 3+ dữ liệu.
 * Cột: A=Mã hàng, B=Tên, C=Giá bán, D=Giá gốc, E=Loại hoa hồng (tiền/%), F=Hoa hồng, G=Hoa hồng tiền.
 */
class CommissionTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            ['BẢNG HOA HỒNG (điền từ dòng 3)'],
            ['Mã hàng', 'Tên sản phẩm', 'Giá bán chung', 'Giá gốc', 'Loại hoa hồng (tiền/%)', 'Hoa hồng', 'Hoa hồng (tiền)'],
        ];
    }

    public function array(): array
    {
        return [
            ['CV-001', 'Cà vạt lụa xanh', 150000, 50000, 'tiền', 6000, 6000],
            ['CV-002', 'Cà vạt nơ đỏ', 200000, 70000, '%', 10, 20000],
        ];
    }
}
