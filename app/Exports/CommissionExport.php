<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CommissionExport implements FromCollection, WithHeadings, WithMapping
{
    /** Định nghĩa cột: key => [tiêu đề, resolver(Product)]. */
    public const COLUMNS = [
        'sku'              => 'Mã hàng',
        'name'             => 'Tên sản phẩm',
        'sale_price'       => 'Giá bán chung',
        'cost_price'       => 'Giá gốc',
        'commission_type'  => 'Loại hoa hồng',
        'commission'       => 'Hoa hồng',
        'commission_value' => 'Hoa hồng (tiền)',
        'profit'           => 'Lợi nhuận tạm tính',
    ];

    protected array $columns;

    public function __construct(?array $columns = null)
    {
        $valid = array_keys(self::COLUMNS);
        $columns = $columns ? array_values(array_intersect($valid, $columns)) : $valid;
        $this->columns = !empty($columns) ? $columns : $valid;
    }

    public function collection()
    {
        return Product::orderBy('sku')->get();
    }

    public function headings(): array
    {
        return array_map(fn ($key) => self::COLUMNS[$key], $this->columns);
    }

    public function map($product): array
    {
        $isPercent = $product->commission_type === 'percent';

        $values = [
            'sku'              => $product->sku,
            'name'             => $product->name,
            'sale_price'       => $product->sale_price,
            'cost_price'       => $product->cost_price,
            'commission_type'  => $isPercent ? '%' : 'tiền',
            'commission'       => $isPercent ? $product->commission_percent : $product->commission_amount,
            'commission_value' => $product->commission_value,
            'profit'           => $product->temp_profit,
        ];

        return array_map(fn ($key) => $values[$key], $this->columns);
    }
}
