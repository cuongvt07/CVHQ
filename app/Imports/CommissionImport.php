<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\TracksImportProgress;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\Cache;

class CommissionImport implements ToCollection, WithEvents
{
    use TracksImportProgress;

    public function collection(Collection $rows)
    {
        $total = $rows->count();
        Log::info("Collection Read Success. Total rows found: {$total}");

        $processed = 0;
        foreach ($rows as $index => $rowData) {
            $rowNumber = $index + 1;
            
            // Log 5 dòng đầu để debug cấu trúc
            if ($rowNumber <= 5) {
                Log::info("DEBUG Row #{$rowNumber}: ", $rowData->toArray());
            }

            // Bỏ qua 2 dòng đầu (Dòng 1: Tiêu đề lớn, Dòng 2: Header)
            if ($rowNumber < 3) {
                continue;
            }

            $sku = $rowData[0] ?? null;
            $commission = $rowData[6] ?? 0;

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
                    Log::info("SUCCESS: Updated SKU: {$sku} at Row #{$rowNumber}");
                } else {
                    Log::warning("NOT FOUND: SKU {$sku} at Row #{$rowNumber}");
                }
            } catch (\Exception $e) {
                Log::error("ERROR at Row #{$rowNumber}: " . $e->getMessage());
                $this->recordError("Dòng {$rowNumber}: " . $e->getMessage());
            }

            $processed++;
            // Cập nhật progress
            $this->updateProgress($processed, $total - 2);
        }
    }

    private function updateProgress($current, $total)
    {
        $progress = Cache::get("import_progress_{$this->importKey}");
        if ($progress) {
            $progress['current'] = $current;
            $progress['total'] = max($current, $total);
            Cache::put("import_progress_{$this->importKey}", $progress, 3600);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Cache::put("import_progress_{$this->importKey}", [
                    'total' => 0, 
                    'current' => 0,
                    'status' => 'processing',
                    'errors' => [],
                ], 3600);
            },
        ];
    }
}
