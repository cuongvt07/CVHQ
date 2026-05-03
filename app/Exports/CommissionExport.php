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
            'Hoa hồng'
        ];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->commission_amount
        ];
    }
}
