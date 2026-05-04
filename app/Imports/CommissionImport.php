<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Log;
use App\Traits\TracksImportProgress;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
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

        // Log row data for debugging
        if ($rowNumber <= 10) {
            Log::info("Processing Row #{$rowNumber}: " . json_encode($rowData));
        }

        // Nhận diện SKU: Ưu tiên tên cột, nếu không có thì dùng cột A (index 0)
        $sku = $rowData['ma_hang'] ?? $rowData['ma_hang_hoa'] ?? $rowData['ma_sp'] ?? $rowData['sku'] ?? ($rowData[0] ?? null);
        
        // Nhận diện Hoa hồng: Ưu tiên tên cột, nếu không có thì dùng cột G (index 6) hoặc C (index 2)
        $commission = $rowData['bang_hoa_hong_chung'] ?? $rowData['hoa_hong_chung'] ?? $rowData['hoa_hong'] ?? $rowData['commission'] ?? ($rowData[6] ?? ($rowData[2] ?? 0));

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



