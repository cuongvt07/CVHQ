<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use Illuminate\Support\Facades\Log;

class CommissionImport implements OnEachRow, WithStartRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        // Row index starts from 1. 
        // WithStartRow(3) means the first row processed will have getIndex() = 3.
        
        Log::info("Processing Row #{$row->getIndex()}: ", $rowData);
        
        // Column A (Index 0): SKU
        // Column G (Index 6): Commission
        $sku = $rowData[0] ?? null;
        $commission = $rowData[6] ?? 0;

        if (!$sku || trim($sku) === '') {
            Log::info("Skipping Row #{$row->getIndex()}: SKU is empty.");
            return; 
        }

        try {
            $product = Product::where('sku', trim($sku))->first();
            
            if ($product) {
                // Xử lý giá tiền (loại bỏ dấu phẩy/chấm nếu có)
                $cleanCommission = str_replace([',', '.'], '', (string)$commission);
                
                $product->update([
                    'commission_amount' => (float) $cleanCommission
                ]);
                Log::info("Updated SKU: {$sku} with Commission: {$cleanCommission}");
            } else {
                Log::warning("SKU not found in database: " . trim($sku));
            }
        } catch (\Exception $e) {
            Log::error("Import Error at Row #{$row->getIndex()}: " . $e->getMessage());
            $this->recordError("Dòng {$row->getIndex()}: " . $e->getMessage());
        }
    }

    public function startRow(): int
    {
        return 3; // Bắt đầu đọc dữ liệu thực tế từ dòng 3
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
