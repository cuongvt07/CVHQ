<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Log;
use App\Traits\TracksImportProgress;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommissionImport implements OnEachRow, WithEvents, ShouldQueue, WithChunkReading, WithStartRow
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $rowNumber = $row->getIndex();

        $sku = $rowData[0] ?? null;
        $commission = $rowData[6] ?? 0;

        if (!$sku || trim((string)$sku) === '') {
            return; 
        }

        try {
            $product = Product::where('sku', trim((string)$sku))->first();
            
            if ($product) {
                $cleanCommission = str_replace([',', '.'], '', (string)$commission);
                $product->update([
                    'commission_amount' => (float) $cleanCommission
                ]);
                // Log::info("SUCCESS: Updated SKU: {$sku} at Row #{$rowNumber}");
            } else {
                // Log::warning("NOT FOUND: SKU {$sku} at Row #{$rowNumber}");
            }
        } catch (\Exception $e) {
            Log::error("ERROR at Row #{$rowNumber}: " . $e->getMessage());
            $this->recordError("Dòng {$rowNumber}: " . $e->getMessage());
        }
    }

    public function startRow(): int
    {
        return 3;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}

