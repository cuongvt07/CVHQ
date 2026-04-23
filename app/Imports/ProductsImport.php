<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Traits\TracksImportProgress;

class ProductsImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    use TracksImportProgress;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $imagePaths = [];
        if (!empty($rowData['hinh_anh_url1url2'])) {
            $urls = explode(',', $rowData['hinh_anh_url1url2']);
            foreach ($urls as $url) {
                $url = trim($url);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    try {
                        $response = Http::get($url);
                        if ($response->successful()) {
                            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = 'products/' . Str::random(20) . '.' . $extension;
                            Storage::disk('public')->put($filename, $response->body());
                            $imagePaths[] = Storage::url($filename);
                        }
                    } catch (\Exception $e) {
                        // Skip if failed
                    }
                }
            }
        }

        $categoryName = $rowData['nhom_hang3_cap'] ?? null;
        $categoryId = null;

        if (!empty($categoryName)) {
            $category = Category::whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first();
            if (!$category) {
                $category = Category::create([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName)
                ]);
            }
            $categoryId = $category->id;
        }

        $sku = $rowData['ma_hang'] ?? null;
        if (!$sku) return;

        $data = [
            'barcode'           => $rowData['ma_vach'] ?? null,
            'name'              => $rowData['ten_hang'] ?? null,
            'product_type'      => $rowData['loai_hang'] ?? null,
            'category_path'     => $rowData['nhom_hang3_cap'] ?? null,
            'category_id'       => $categoryId,
            'brand'             => $rowData['thuong_hieu'] ?? null,
            'cost_price'        => $rowData['gia_von'] ?? 0,
            'sale_price'        => $rowData['gia_ban'] ?? 0,
            'stock_quantity'    => $rowData['ton_kho'] ?? 999,
            'reserved_quantity' => $rowData['kh_dat'] ?? 0,
            'min_stock'         => $rowData['ton_nho_nhat'] ?? 0,
            'max_stock'         => $rowData['ton_lon_nhat'] ?? 0,
            'unit'              => $rowData['dvt'] ?? null,
            'base_unit_code'    => $rowData['ma_dvt_co_ban'] ?? null,
            'conversion_rate'   => $rowData['quy_doi'] ?? 1,
            'attributes'        => $rowData['thuoc_tinh'] ?? null,
            'related_sku'       => $rowData['ma_hh_lien_quan'] ?? null,
            'weight'            => $rowData['trong_luong'] ?? 0,
            'is_active'         => ($rowData['dang_kinh_doanh'] ?? 1) == 1,
            'is_direct_sale'    => ($rowData['duoc_ban_truc_tiep'] ?? 1) == 1,
            'description'       => $rowData['mo_ta'] ?? null,
            'note_template'     => $rowData['mau_ghi_chu'] ?? null,
            'location'          => $rowData['vi_tri'] ?? null,
            'is_combo'          => !empty($rowData['hang_thanh_phan']),
        ];

        if (!empty($imagePaths)) {
            $data['images'] = $imagePaths;
        }

        $product = Product::withTrashed()->firstOrNew(['sku' => $sku]);
        
        if ($product->exists && $product->trashed()) {
            $product->restore();
        }

        $product->fill($data);
        $product->save();
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
