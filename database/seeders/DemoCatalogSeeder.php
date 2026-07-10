<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Dọn danh mục demo:
 *  - Ẩn (soft-delete) mọi sản phẩm KHÔNG phải cà vạt (giữ nguyên lịch sử hoá đơn).
 *  - Thêm 4 danh mục mới + 10 sản phẩm mỗi danh mục (thông tin/giá tự bịa, không ảnh).
 *
 * Chạy: php artisan db:seed --class=Database\\Seeders\\DemoCatalogSeeder
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ẩn sản phẩm không phải cà vạt.
        $hidden = DB::table('products')
            ->whereNull('deleted_at')
            ->where('category_path', 'not like', 'Cà vạt%')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        // 2) Danh mục + sản phẩm mới.
        $catalog = [
            'Khuy măng sét' => [
                'prefix' => 'KMS', 'unit' => 'Đôi', 'min' => 150000, 'max' => 450000,
                'names' => [
                    'Khuy măng sét bạc trơn cao cấp', 'Khuy măng sét mạ vàng gold', 'Khuy măng sét đá onyx đen',
                    'Khuy măng sét khắc chữ theo yêu cầu', 'Khuy măng sét mặt vuông cổ điển', 'Khuy măng sét mặt tròn',
                    'Khuy măng sét xà cừ trắng', 'Khuy măng sét hình bánh lái', 'Khuy măng sét titan phay xước',
                    'Khuy măng sét đính pha lê',
                ],
            ],
            'Pocket square' => [
                'prefix' => 'PSQ', 'unit' => 'Cái', 'min' => 90000, 'max' => 250000,
                'names' => [
                    'Pocket square lụa trơn xanh navy', 'Pocket square chấm bi trắng', 'Pocket square hoạ tiết Paisley',
                    'Pocket square kẻ caro nâu', 'Pocket square trắng viền đen', 'Pocket square đỏ đô sang trọng',
                    'Pocket square hoạ tiết hoa', 'Pocket square satin xám bạc', 'Pocket square cotton kẻ sọc',
                    'Pocket square lụa in nghệ thuật',
                ],
            ],
            'Tất nam' => [
                'prefix' => 'TAT', 'unit' => 'Đôi', 'min' => 45000, 'max' => 120000,
                'names' => [
                    'Tất nam cổ trung trơn màu', 'Tất nam kẻ sọc công sở', 'Tất nam chấm bi lịch lãm',
                    'Tất nam cotton co giãn', 'Tất nam len giữ ấm mùa đông', 'Tất nam cổ cao trơn',
                    'Tất nam hoạ tiết caro', 'Tất nam thể thao thấm hút', 'Tất nam lười (tất ẩn)',
                    'Tất nam sợi tre kháng khuẩn',
                ],
            ],
            'Ví da' => [
                'prefix' => 'VID', 'unit' => 'Cái', 'min' => 350000, 'max' => 1200000,
                'names' => [
                    'Ví da bò gập đôi màu nâu', 'Ví da đứng dọc màu đen', 'Ví da khắc tên theo yêu cầu',
                    'Ví da nhiều ngăn đựng thẻ', 'Ví da mini cầm tay', 'Ví da Saffiano vân nổi',
                    'Ví da có khóa kéo', 'Ví da đựng name card', 'Ví da handmade thủ công',
                    'Ví clutch da cao cấp',
                ],
            ],
        ];

        $newProducts = 0;
        foreach ($catalog as $catName => $cfg) {
            // Danh mục (unique theo name).
            DB::table('categories')->updateOrInsert(
                ['name' => $catName],
                ['slug' => Str::slug($catName) . '-' . Str::random(4), 'updated_at' => now(), 'created_at' => now()]
            );
            $catId = DB::table('categories')->where('name', $catName)->value('id');

            // Xoá SP demo cũ cùng prefix (chạy lại không trùng).
            DB::table('products')->where('sku', 'like', $cfg['prefix'] . '-%')->delete();

            foreach ($cfg['names'] as $i => $name) {
                $sale = (int) (round(mt_rand($cfg['min'], $cfg['max']) / 5000) * 5000);
                $cost = (int) (round($sale * mt_rand(50, 68) / 100 / 1000) * 1000);
                $sku = $cfg['prefix'] . '-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

                DB::table('products')->insert([
                    'sku' => $sku,
                    'base_name' => $name,
                    'name' => $name,
                    'product_type' => 'Hàng hóa',
                    'category_path' => $catName,
                    'category_id' => $catId,
                    'brand' => 'CVHQ',
                    'cost_price' => $cost,
                    'sale_price' => $sale,
                    'commission_amount' => 0,
                    'stock_quantity' => mt_rand(20, 300),
                    'reserved_quantity' => 0,
                    'min_stock' => 5,
                    'max_stock' => 999999,
                    'unit' => $cfg['unit'],
                    'conversion_rate' => 1,
                    'is_active' => 1,
                    'is_direct_sale' => 1,
                    'is_combo' => 0,
                    'images' => json_encode([]),
                    'location' => 'Kệ mới',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $newProducts++;
            }
        }

        $this->command?->info("Đã ẩn {$hidden} SP không phải cà vạt; tạo 4 danh mục + {$newProducts} SP mới.");
    }
}
