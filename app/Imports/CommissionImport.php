<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\BeforeImport;

class CommissionImport implements OnEachRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $index = $row->getIndex();
        
        // Log MỌI DÒNG để debug
        Log::info("DEBUG Row #{$index}: ", $rowData);

        // Chỉ xử lý từ dòng 3 trở đi như user yêu cầu
        if ($index < 3) {
            return;
        }

        // Column A (Index 0): SKU
        // Column G (Index 6): Commission
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
                Log::info("SUCCESS: Updated SKU: {$sku} with Commission: {$cleanCommission}");
            } else {
                Log::warning("NOT FOUND: SKU {$sku} at Row #{$index}");
            }
        } catch (\Exception $e) {
            Log::error("ERROR at Row #{$index}: " . $e->getMessage());
            $this->recordError("Dòng {$index}: " . $e->getMessage());
        }
    }

    public function registerEvents(): array
    {
        $parentEvents = $this->traitRegisterEvents(); // Gọi events từ trait

        return array_merge($parentEvents, [
            BeforeImport::class => function (BeforeImport $event) {
                $reader = $event->getReader();
                $sheetNames = array_keys($reader->getTotalRows());
                Log::info("Excel Sheets found: " . implode(", ", $sheetNames));
                
                // Gọi logic của trait
                $totalRows = $reader->getTotalRows();
                $total = 0;
                foreach ($totalRows as $sheetName => $rowCount) {
                    $total += $rowCount; // Đếm tất cả các dòng
                }
                
                Log::info("Total Raw Rows: {$total}");

                \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importKey}", [
                    'total' => max(0, $total - 2), // Trừ đi 2 dòng đầu
                    'current' => 0,
                    'status' => 'processing',
                    'errors' => [],
                ], 3600);
            },
        ]);
    }

    // Rewrite lại traitRegisterEvents vì trong trait nó đã có registerEvents rồi
    // Tuy nhiên PHP không cho gọi parent trait method dễ dàng nếu trùng tên
    // Nên tôi sẽ copy logic vào đây luôn cho chắc chắn.
    
    public function chunkSize(): int
    {
        return 100;
    }
}
