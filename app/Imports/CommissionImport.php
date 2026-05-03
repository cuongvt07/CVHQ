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
        Log::info("Processing Row #{$row->getIndex()}: ", $rowData);
        
        $sku = $rowData['ma_hang'] ?? $rowData['ma_sp'] ?? $rowData['sku'] ?? $rowData['ma_hang_hoa'] ?? null;
        $commission = $rowData['bang_hoa_hong_chung'] ?? $rowData['hoa_hong'] ?? $rowData['commission'] ?? 0;

        if (!$sku) {
            return; 
        }

        try {
            $product = Product::where('sku', trim($sku))->first();
            
            if ($product) {
                $product->update([
                    'commission_amount' => (float) $commission
                ]);
                Log::info("Updated SKU: {$sku} with Commission: {$commission}");
            } else {
                Log::warning("SKU not found in database: " . trim($sku));
            }
        } catch (\Exception $e) {
            Log::error("Import Error at Row #{$row->getIndex()}: " . $e->getMessage());
            $this->recordError("Dòng {$row->getIndex()}: " . $e->getMessage());
        }
    }

    public function headingRow(): int
    {
        return 2; // Tiêu đề ở dòng 2, dữ liệu bắt đầu từ dòng 3
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
