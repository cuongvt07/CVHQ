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
use Maatwebsite\Excel\Events\AfterChunk;
use Illuminate\Support\Facades\Cache;

class CommissionImport implements OnEachRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $index = $row->getIndex();
        
        Log::info("DEBUG Row #{$index}: ", $rowData);

        if ($index < 3) {
            return;
        }

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

        // Update progress in cache for each row
        $progress = Cache::get("import_progress_{$this->importKey}");
        if ($progress) {
            $progress['current']++;
            Cache::put("import_progress_{$this->importKey}", $progress, 3600);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $reader = $event->getReader();
                $totalRows = $reader->getTotalRows();
                $sheetNames = array_keys($totalRows);
                
                Log::info("Excel Sheets found: " . implode(", ", $sheetNames));
                
                $total = 0;
                foreach ($totalRows as $sheetName => $rowCount) {
                    $total += $rowCount;
                }
                
                Log::info("Total Raw Rows: {$total}");

                Cache::put("import_progress_{$this->importKey}", [
                    'total' => max(0, $total - 2), 
                    'current' => 0,
                    'status' => 'processing',
                    'errors' => [],
                ], 3600);
            },
            AfterChunk::class => function (AfterChunk $event) {
                // Logic progress đã được xử lý trong onRow hoặc AfterChunk tùy setup
                // Ở đây onRow đã tự update current++
            }
        ];
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
