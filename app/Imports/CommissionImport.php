<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;

class CommissionImport implements ToCollection, WithStartRow
{
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        Log::info("CommissionImport: Processing {$rows->count()} rows");

        foreach ($rows as $row) {
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
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } catch (\Exception $e) {
                Log::error("CommissionImport ERROR: SKU={$sku}, " . $e->getMessage());
                $this->errors[] = "SKU {$sku}: " . $e->getMessage();
            }
        }

        Log::info("CommissionImport: Updated={$this->updatedCount}, Skipped={$this->skippedCount}, Errors=" . count($this->errors));
    }

    public function startRow(): int
    {
        return 3; // Dòng 1 = tiêu đề bảng, Dòng 2 = header cột, Dòng 3+ = dữ liệu
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
