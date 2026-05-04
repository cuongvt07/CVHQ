<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Log;
use App\Traits\TracksImportProgress;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommissionImport implements OnEachRow, WithEvents, ShouldQueue, WithChunkReading, WithHeadingRow
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $rowNumber = $row->getIndex();

        // Log row data for debugging on the first few rows
        if ($rowNumber <= 5) {
            Log::info("Row #{$rowNumber} data: " . json_encode($rowData));
        }

        // Nhận diện SKU từ nhiều tên cột khả thi (thêm các trường phổ biến từ KiotViet)
        $sku = $rowData['ma_hang'] ?? $rowData['ma_hang_hoa'] ?? $rowData['ma_sp'] ?? $rowData['sku'] ?? $rowData[0] ?? null;
        
        // Nhận diện Hoa hồng từ nhiều tên cột khả thi
        $commission = $rowData['bang_hoa_hong_chung'] ?? $rowData['hoa_hong_chung'] ?? $rowData['hoa_hong'] ?? $rowData['commission'] ?? $rowData[6] ?? 0;

        if (!$sku || trim((string)$sku) === '') {
            return; 
        }

        try {
            $product = Product::where('sku', trim((string)$sku))->first();
            
            if ($product) {
                // Xử lý định dạng số (loại bỏ dấu phân cách nếu có)
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

    public function headingRow(): int
    {
        return 2; // Tiêu đề ở dòng 2, dữ liệu sẽ bắt đầu từ dòng 3
    }

    public function chunkSize(): int
    {
        return 100;
    }
}


