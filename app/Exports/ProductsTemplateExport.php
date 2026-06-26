<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * File Excel MẪU cho import sản phẩm — header trùng với heading-row mà
 * ProductRowImporter đọc (snake_case hoá tự động), kèm 1 dòng ví dụ.
 */
class ProductsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Mã hàng', 'Mã vạch', 'Tên hàng', 'Loại hàng', 'Nhóm hàng(3 Cấp)', 'Thương hiệu',
            'Giá vốn', 'Giá bán', 'Tồn kho', 'KH đặt', 'Tồn nhỏ nhất', 'Tồn lớn nhất',
            'ĐVT', 'Mã ĐVT cơ bản', 'Quy đổi', 'Mã HH liên quan', 'Trọng lượng',
            'Đang kinh doanh', 'Được bán trực tiếp', 'Vị trí', 'Thuộc tính', 'Hàng thành phần',
            'Mô tả', 'Mẫu ghi chú', 'Hình ảnh (URL1,URL2)',
        ];
    }

    public function array(): array
    {
        return [
            [
                'CV-001', '8938000000001', 'Cà vạt lụa xanh', 'Hàng hóa', 'Cà vạt nam', 'CVHQ',
                50000, 150000, 100, 0, 0, 0,
                'Cái', '', 1, '', 0,
                1, 1, 'Kệ A1', 'MÀU SẮC:Xanh|CHẤT LIỆU:Lụa', '',
                'Cà vạt cao cấp', '', '',
            ],
        ];
    }
}
