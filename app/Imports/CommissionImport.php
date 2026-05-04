<?php
3: 
4: namespace App\Imports;
5: 
6: use App\Models\Product;
7: use Maatwebsite\Excel\Concerns\OnEachRow;
8: use Maatwebsite\Excel\Row;
9: use Illuminate\Support\Facades\Log;
10: use App\Traits\TracksImportProgress;
11: use Maatwebsite\Excel\Concerns\WithEvents;
12: use Maatwebsite\Excel\Concerns\WithStartRow;
13: use Maatwebsite\Excel\Concerns\WithChunkReading;
14: use Illuminate\Contracts\Queue\ShouldQueue;
15: 
16: class CommissionImport implements OnEachRow, WithEvents, ShouldQueue, WithChunkReading, WithStartRow
17: {
18:     use TracksImportProgress;
19: 
20:     public function onRow(Row $row)
21:     {
22:         $rowData = $row->toArray();
23:         $rowNumber = $row->getIndex();
24: 
25:         $sku = $rowData[0] ?? null;
26:         $commission = $rowData[6] ?? 0;
27: 
28:         if (!$sku || trim((string)$sku) === '') {
29:             return; 
30:         }
31: 
32:         try {
33:             $product = Product::where('sku', trim((string)$sku))->first();
34:             
35:             if ($product) {
36:                 $cleanCommission = str_replace([',', '.'], '', (string)$commission);
37:                 $product->update([
38:                     'commission_amount' => (float) $cleanCommission
39:                 ]);
40:                 // Log::info("SUCCESS: Updated SKU: {$sku} at Row #{$rowNumber}");
41:             } else {
42:                 // Log::warning("NOT FOUND: SKU {$sku} at Row #{$rowNumber}");
43:             }
44:         } catch (\Exception $e) {
45:             Log::error("ERROR at Row #{$rowNumber}: " . $e->getMessage());
46:             $this->recordError("Dòng {$rowNumber}: " . $e->getMessage());
47:         }
48:     }
49: 
50:     public function startRow(): int
51:     {
52:         return 3;
53:     }
54: 
55:     public function chunkSize(): int
56:     {
57:         return 100;
58:     }
59: }
