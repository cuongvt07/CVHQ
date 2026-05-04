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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommissionImport implements OnEachRow, WithEvents, ShouldQueue, WithChunkReading, WithStartRow, WithMultipleSheets
{
    use TracksImportProgress;

    public function sheets(): array
    {
        return [
            0 => $this, // Ép buộc đọc sheet đầu tiên (index 0)
        ];
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $rowNumber = $row->getIndex();

        // Log dữ liệu để debug
        Log::info("Processing Row #{$rowNumber}: " . json_encode($rowData));

        // Nhận diện SKU: Cột A (0)
        $sku = $rowData[0] ?? null;
        
        // Nhận diện Hoa hồng: Cột G (6) hoặc Cột C (2)
        $commission = $rowData[6] ?? ($rowData[2] ?? 0);

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




