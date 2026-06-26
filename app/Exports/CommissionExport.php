<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CommissionExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::all();
    }

    public function headings(): array
    {
        return [
            'Mã hàng',
            'Tên sản phẩm',
            'Giá bán chung',
            'Giá gốc',
            'Loại hoa hồng',     // "tiền" | "%"
            'Hoa hồng',          // số tiền hoặc số % tùy loại
            'Hoa hồng (tiền)',   // giá trị đã quy đổi ra tiền
        ];
    }

    public function map($product): array
    {
        $isPercent = $product->commission_type === 'percent';
        return [
            $product->sku,
            $product->name,
            $product->sale_price,
            $product->cost_price,
            $isPercent ? '%' : 'tiền',
            $isPercent ? $product->commission_percent : $product->commission_amount,
            $product->commission_value,
        ];
    }
}
