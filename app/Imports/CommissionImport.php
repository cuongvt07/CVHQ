<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use Illuminate\Support\Facades\Log;

class CommissionImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $sku = $rowData['ma_hang'] ?? $rowData['sku'] ?? null;
        $commission = $rowData['hoa_hong'] ?? $rowData['commission'] ?? 0;

        if (!$sku) {
            return; // Skip if no SKU
        }

        try {
            $product = Product::where('sku', $sku)->first();
            
            if ($product) {
                $product->update([
                    'commission_amount' => (float) $commission
                ]);
            } else {
                // SKU not found, continue (skip) as requested by user
                // Optional: record a warning or just skip
                // $this->recordError("SKU {$sku} không tồn tại trên hệ thống.");
            }
        } catch (\Exception $e) {
            $this->recordError("Dòng {$row->getIndex()}: " . $e->getMessage());
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function tries(): int
    {
        return 3;
    }
}
