<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommissionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $sku = $row['ma_hang'] ?? $row['sku'] ?? null;
        $commission = $row['hoa_hong'] ?? $row['commission'] ?? 0;

        if ($sku) {
            Product::where('sku', $sku)->update([
                'commission_amount' => (float) $commission
            ]);
        }

        return null;
    }
}
