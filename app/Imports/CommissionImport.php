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

            // Định dạng mới (export của hệ thống): E(4)=Loại hoa hồng, F(5)=Hoa hồng.
            // Định dạng cũ (KiotViet): G(6)=Bảng hoa hồng chung (tiền).
            $typeCell = strtolower(trim((string)($row[4] ?? '')));
            $isPercent = in_array($typeCell, ['%', 'percent', 'phan tram', 'phần trăm'], true);
            $isAmount  = in_array($typeCell, ['tien', 'tiền', 'amount', 'vnd', 'vnđ'], true);

            if (!$sku || trim((string)$sku) === '') {
                continue;
            }

            try {
                $product = Product::where('sku', trim((string)$sku))->first();

                if ($product) {
                    if ($isPercent) {
                        // Giá trị % ở cột F (5)
                        $pct = max(0, min(100, (float) str_replace(',', '.', (string)($row[5] ?? 0))));
                        $product->commission_type = 'percent';
                        $product->commission_percent = $pct;
                        $product->commission_amount = (int) round(((float) $product->sale_price) * $pct / 100);
                    } else {
                        // Tiền: ưu tiên cột F (5) ở định dạng mới, fallback cột G (6) định dạng cũ.
                        $raw = $isAmount ? ($row[5] ?? 0) : ($row[6] ?? ($row[5] ?? 0));
                        $cleanCommission = str_replace([',', '.'], '', (string)$raw);
                        $product->commission_type = 'amount';
                        $product->commission_amount = (int) $cleanCommission;
                        $product->commission_percent = 0;
                    }
                    $product->save();
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
