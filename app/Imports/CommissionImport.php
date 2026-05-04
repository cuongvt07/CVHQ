<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\Log;
use App\Traits\TracksImportProgress;

class CommissionImport implements ToCollection, WithStartRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function collection(Collection $rows)
    {
        Log::info("CommissionImport processing batch of {$rows->count()} rows");

        foreach ($rows as $index => $row) {
            // Cột A (0) = Mã hàng (SKU)
            $sku = $row[0] ?? null;
            // Cột G (6) = Bảng hoa hồng chung
            $commission = $row[6] ?? 0;

            if (!$sku || trim((string)$sku) === '') {
                continue;
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
                Log::error("CommissionImport ERROR: SKU={$sku}, " . $e->getMessage());
                $this->recordError("SKU {$sku}: " . $e->getMessage());
            }
        }
    }

    public function startRow(): int
    {
        return 3; // Dòng 1 = tiêu đề bảng, Dòng 2 = header cột, Dòng 3+ = dữ liệu
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
