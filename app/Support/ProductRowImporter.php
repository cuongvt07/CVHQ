<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Upsert 1 sản phẩm từ 1 dòng Excel (heading-row snake_case).
 * Dùng cho import đồng bộ theo chunk. Throw \Exception khi lỗi.
 */
class ProductRowImporter
{
    public static function import(array $rowData): void
    {
        $sku = trim((string) ($rowData['ma_hang'] ?? ''));
        if ($sku === '') {
            throw new \RuntimeException('Thiếu mã hàng (cột "Mã hàng").');
        }

        // Ảnh: tải từ URL (nếu có), bỏ qua URL lỗi.
        $imagePaths = [];
        if (!empty($rowData['hinh_anh_url1url2'])) {
            foreach (explode(',', (string) $rowData['hinh_anh_url1url2']) as $url) {
                $url = trim($url);
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }
                try {
                    $response = Http::timeout(10)->get($url);
                    if ($response->successful()) {
                        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $filename = 'products/' . Str::random(20) . '.' . $extension;
                        Storage::disk('public')->put($filename, $response->body());
                        $imagePaths[] = Storage::url($filename);
                    }
                } catch (\Throwable $e) {
                    // Ảnh lỗi không làm hỏng cả dòng.
                }
            }
        }

        // Danh mục.
        $categoryId = null;
        $categoryName = $rowData['nhom_hang3_cap'] ?? null;
        if (!empty($categoryName)) {
            $category = Category::whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first();
            if (!$category) {
                $category = Category::create([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                ]);
            }
            $categoryId = $category->id;
        }

        $data = [
            'barcode'           => $rowData['ma_vach'] ?? null,
            'base_name'         => $rowData['ten_hang'] ?? null,
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

        // Thuộc tính Key:Value | Key:Value, gộp với thuộc tính cũ.
        $existingAttributes = ($product->exists && is_array($product->attributes)) ? $product->attributes : [];
        $parsedAttributes = $existingAttributes;
        $newAttributesStr = $rowData['thuoc_tinh'] ?? '';
        if (!empty($newAttributesStr)) {
            foreach (explode('|', $newAttributesStr) as $attr) {
                $parts = explode(':', $attr, 2);
                if (count($parts) >= 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    if ($key === '') {
                        continue;
                    }
                    if (isset($parsedAttributes[$key])) {
                        $currentValues = array_map('trim', explode(',', (string) $parsedAttributes[$key]));
                        if (!in_array($value, $currentValues, true)) {
                            $parsedAttributes[$key] .= ', ' . $value;
                        }
                    } else {
                        $parsedAttributes[$key] = $value;
                    }
                }
            }
        }
        $data['attributes'] = $parsedAttributes;

        $product->fill($data);

        if ($product->isDirty('stock_quantity')) {
            $before = (int) $product->getOriginal('stock_quantity', 0);
            $change = (int) $product->stock_quantity - $before;
            if ($change !== 0) {
                $product->recordStockHistory('Import', $change, null, null, 'Nhập từ file Excel', $before);
            }
        }

        $product->save();
    }
}
